<?php

namespace App\Output;

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

        $issues = $this->getIssues($changes);

        render((string) view('summary', [
            'issues' => $issues,
            'totalFiles' => $totalFiles,
            'details' => $this->getDetails($changes),
            'dryRun' => $this->input->getOption('dry-run'),
            'failing' => $issues->contains(fn (Issue $issue) => $issue->failing()),
        ]));

        foreach ($issues as $issue) {
            render((string) view('issue.show', [
                'issue' => $issue,
                'isVerbose' => $this->output->isVerbose(),
            ]));
        }

        $this->output->writeln('');
    }

    /**
     * Gets the description of each kind of change.
     */
    public function getDetails(array $changes): array
    {
        $count = fn (callable $filter) => collect($changes)->filter($filter)->sum('count');

        $details = [
            'Missing %d %s' => $count(fn ($change) => ($change['check'] ?? false) && ! ($change['unused'] ?? false)),
            'New %d %s scanned' => $count(fn ($change) => ! ($change['check'] ?? false) && ! ($change['unused'] ?? false)),
            '%d unused %s' => $count(fn ($change) => ($change['check'] ?? false) && ($change['unused'] ?? false)),
            'Removed %d unused %s' => $count(fn ($change) => ! ($change['check'] ?? false) && ($change['unused'] ?? false)),
        ];

        return collect($details)
            ->filter()
            ->map(fn ($total, $format) => sprintf($format, $total, str('translation')->plural($total)))
            ->values()
            ->all();
    }

    /**
     * Gets the list of issues from the given changes.
     */
    public function getIssues(array $changes): Collection
    {
        return collect($changes)->filter(function ($change) {
            return $change['count'] > 0;
        })->map(fn ($change) => new Issue(
            path: Project::path(),
            file: data_get($change, 'file'),
            count: data_get($change, 'count'),
            changes: data_get($change, 'issues'),
            check: data_get($change, 'check', false),
            unused: data_get($change, 'unused', false),
        ))->values();
    }
}
