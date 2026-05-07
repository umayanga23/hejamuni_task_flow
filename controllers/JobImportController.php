<?php
/**
 * TaskFlow Pro — Job Import Controller
 * Handles WhatsApp multi-job parsing via Claude API + bulk save.
 *
 * ROUTES to add in your router:
 *   GET  /job-import          → JobImportController::showImport
 *   POST /job-import/parse    → JobImportController::parse
 *   POST /job-import/save     → JobImportController::saveBulk
 *
 * CONFIG: Add to config/config.php:
 *   define('ANTHROPIC_API_KEY', 'sk-ant-...');
 */
class JobImportController
{
    // ── SHOW PAGE ─────────────────────────────────────────────────────────────

    public function showImport(): void
    {
        requireAuth();
        $categories    = (new CategoryModel())->getAll(auth()['id']);
        $recentImports = $this->getRecentImports();
        require VIEW_PATH . '/pages/job_import.php';
    }

    // ── PARSE (Ajax POST) ─────────────────────────────────────────────────────

    public function parse(): void
    {
        requireAuth();
        header('Content-Type: application/json');

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $body['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!verifyCsrf($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $message = trim($body['message'] ?? '');
        if (!$message) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No message provided']);
            exit;
        }

        $result = $this->extractJobsWithClaude($message);

        if (!$result['success']) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $result['error']]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'jobs'    => $result['jobs'],
            'count'   => count($result['jobs']),
        ]);
        exit;
    }

    // ── BULK SAVE (Ajax POST) ─────────────────────────────────────────────────

    public function saveBulk(): void
    {
        requireAuth();
        header('Content-Type: application/json');

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $body['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!verifyCsrf($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }

        $jobs       = $body['jobs']        ?? [];
        $categoryId = ($body['category_id'] ?? '') !== '' ? (int)$body['category_id'] : null;

        if (empty($jobs)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No jobs selected']);
            exit;
        }

        $taskModel = new TaskModel();
        $saved     = 0;
        $errors    = [];

        foreach ($jobs as $job) {
            $title = trim($job['title'] ?? '');
            if (!$title) continue;

            // Build notes block
            $noteParts = ['Source: WhatsApp group'];
            if (!empty($job['url']))     $noteParts[] = 'Apply URL: ' . $job['url'];
            if (!empty($job['type']))    $noteParts[] = 'Job type: ' . $job['type'];
            if (!empty($job['level']))   $noteParts[] = 'Level: ' . $job['level'];
            if (!empty($job['company'])) $noteParts[] = 'Company: ' . $job['company'];
            if (!empty($job['salary']))  $noteParts[] = 'Salary: ' . $job['salary'];

            $description = trim($job['description'] ?? '');
            if (!empty($job['skills'])) {
                $description .= ($description ? "\n\n" : '') . 'Skills: ' . implode(', ', (array)$job['skills']);
            }

            try {
                $taskModel->create([
                    'user_id'     => auth()['id'],
                    'title'       => $title,
                    'description' => $description,
                    'priority'    => $job['priority']    ?? 'medium',
                    'status'      => 'pending',
                    'category_id' => $categoryId,
                    'due_date'    => $job['deadline']    ?? null,
                    'notes'       => implode("\n", $noteParts),
                ]);
                $saved++;
            } catch (Exception $e) {
                $errors[] = $title . ': ' . $e->getMessage();
            }
        }

        echo json_encode([
            'success' => true,
            'saved'   => $saved,
            'errors'  => $errors,
        ]);
        exit;
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────

    /**
     * Call Claude API to extract structured job list from raw WhatsApp text.
     */
    private function extractJobsWithClaude(string $message): array
    {
        if (!defined('ANTHROPIC_API_KEY') || !ANTHROPIC_API_KEY) {
            return ['success' => false, 'error' => 'ANTHROPIC_API_KEY not configured in config.php'];
        }

        $systemPrompt = <<<'PROMPT'
You are a job listing extractor. Given a WhatsApp message containing one or more job vacancies, extract every job and return a JSON array only — no markdown, no explanation.

Each job object must include:
- "title"       : string — the job title (e.g. "Senior Software Engineer")
- "company"     : string — company name if mentioned, else ""
- "url"         : string — the job application URL (jobhunder.com or similar), else ""
- "type"        : string — "Remote", "On-site", "Hybrid", or "" if unknown
- "level"       : string — "Intern", "Associate", "Mid-level", "Senior", or "" if unknown
- "description" : string — one sentence summary based on the title/context
- "skills"      : array of strings — tech skills mentioned, else []
- "salary"      : string — salary if mentioned, else ""
- "deadline"    : string — ISO date YYYY-MM-DD if deadline mentioned, else null
- "priority"    : string — "high" for Senior roles, "medium" for mid/associate, "low" for interns

Return ONLY a valid JSON array. No other text.
PROMPT;

        $payload = json_encode([
            'model'      => 'claude-sonnet-4-20250514',
            'max_tokens' => 2000,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: '          . ANTHROPIC_API_KEY,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $raw      = curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'error' => 'Network error: ' . $curlErr];
        }

        $data = json_decode($raw, true);

        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? 'Claude API error (HTTP ' . $httpCode . ')';
            return ['success' => false, 'error' => $msg];
        }

        $text = $data['content'][0]['text'] ?? '';

        // Strip accidental markdown fences
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);

        $jobs = json_decode(trim($text), true);

        if (!is_array($jobs)) {
            return ['success' => false, 'error' => 'AI returned unexpected format. Try again.'];
        }

        // Sanitise each job
        foreach ($jobs as &$job) {
            $job['title']       = strip_tags(trim($job['title']       ?? ''));
            $job['company']     = strip_tags(trim($job['company']     ?? ''));
            $job['url']         = filter_var(trim($job['url'] ?? ''), FILTER_SANITIZE_URL);
            $job['type']        = strip_tags(trim($job['type']        ?? ''));
            $job['level']       = strip_tags(trim($job['level']       ?? ''));
            $job['description'] = strip_tags(trim($job['description'] ?? ''));
            $job['salary']      = strip_tags(trim($job['salary']      ?? ''));
            $job['deadline']    = !empty($job['deadline']) ? $job['deadline'] : null;
            $job['priority']    = in_array($job['priority'] ?? '', ['critical','high','medium','low'])
                                  ? $job['priority'] : 'medium';
            $job['skills']      = array_map('strip_tags', (array)($job['skills'] ?? []));
        }
        unset($job);

        return ['success' => true, 'jobs' => array_values($jobs)];
    }

    /**
     * Fetch the 5 most recently imported job-lead tasks.
     */
    private function getRecentImports(): array
    {
        try {
            return Database::query(
                "SELECT id, title, status, created_at
                 FROM   tasks
                 WHERE  user_id  = :uid
                   AND  notes    LIKE '%Source: WhatsApp group%'
                 ORDER  BY created_at DESC
                 LIMIT  5",
                [':uid' => auth()['id']]
            )->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}