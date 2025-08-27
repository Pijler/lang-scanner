<?php

namespace App\Output;

use App\Enum\Status;
use App\Output\Concerns\InteractsWithSymbols;
use App\Project;
use App\ValueObjects\Issue;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\render;
use function Termwind\renderUsing;

class SummaryOutput
{
    use InteractsWithSymbols;

    /**
     * Creates a new Summary Output instance.
     */
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {}

    /**
     * Handle the given report summary.
     */
    public function handle(int $totalFiles, array $changes): void
    {
        renderUsing($this->output);

        $issues = $this->getIssues(Project::path(), $changes);

        render((string) view('summary', [
            'issues' => $issues,
            'totalFiles' => $totalFiles,
            'testing' => $this->input->getOption('check'),
        ]));

        foreach ($issues as $issue) {
            render((string) view('issue.show', [
                'issue' => $issue,
                'isVerbose' => $this->output->isVerbose(),
            ]));

            if ($this->output->isVerbose() && $issue->code()) {
                $this->output->writeln($issue->code());
            }
        }

        $this->output->writeln('');
    }

    /**
     * Gets the list of issues from the given changes.
     */
    public function getIssues(string $path, array $changes): Collection
    {
        return collect($changes)->filter(function ($change) {
            return $change['count'] > 0;
        })->map(fn ($change) => new Issue(
            path: $path,
            file: $change['file'],
            count: $change['count'],
            changes: $change['issues'],
            status: $this->getIssueStatus($change),
        ))->values();
    }

    /**
     * Gets the issue status based on the change details.
     */
    private function getIssueStatus(array $change): Status
    {
        if ($this->input->getOption('check')) {
            return Status::ERROR;
        }

        if (isset($change['check_only']) && $change['check_only']) {
            return Status::SKIPPED;
        }

        return Status::OK;
    }
}
