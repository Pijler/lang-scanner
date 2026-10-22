<?php

use App\Repositories\GitPathsRepository;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

beforeEach(function () {
    $this->repo = realpath(sys_get_temp_dir()).'/lang-scanner-git-'.uniqid();

    File::ensureDirectoryExists($this->repo.'/sub');

    $this->git = function (string ...$args) {
        $process = new Process(['git', '-c', 'user.name=Test', '-c', 'user.email=test@example.com', ...$args], $this->repo);

        $process->mustRun();
    };

    File::put($this->repo.'/sub/kept.php', 'a');
    File::put($this->repo.'/sub/old.php', 'a');
    File::put($this->repo.'/sub/deleted.php', 'a');

    ($this->git)('init', '-q', '-b', 'main');
    ($this->git)('add', '.');
    ($this->git)('commit', '-q', '-m', 'initial');
});

afterEach(function () {
    File::deleteDirectory($this->repo);
});

test('it should return dirty files relative to the repository root', function () {
    File::put($this->repo.'/sub/kept.php', 'b');
    File::put($this->repo.'/sub/with space.php', 'a');
    File::delete($this->repo.'/sub/deleted.php');

    ($this->git)('mv', 'sub/old.php', 'sub/new.php');

    $files = (new GitPathsRepository($this->repo.'/sub'))->dirty();

    sort($files);

    expect($files)->toBe([
        $this->repo.'/sub/kept.php',
        $this->repo.'/sub/new.php',
        $this->repo.'/sub/with space.php',
    ]);
});

test('it should return diff files relative to the repository root', function () {
    ($this->git)('checkout', '-q', '-b', 'feature');

    File::put($this->repo.'/sub/committed.php', 'a');

    ($this->git)('add', '.');
    ($this->git)('commit', '-q', '-m', 'feature');

    File::put($this->repo.'/sub/kept.php', 'b');
    File::put($this->repo.'/sub/untracked.php', 'a');

    $files = (new GitPathsRepository($this->repo.'/sub'))->diff('main');

    sort($files);

    expect($files)->toBe([
        $this->repo.'/sub/committed.php',
        $this->repo.'/sub/kept.php',
        $this->repo.'/sub/untracked.php',
    ]);
});
