<?php

use App\Actions\Base\DuplicateScanner;

beforeEach(function () {
    DuplicateScanner::$cache = [];
});

test('it should run default command (test-j)', function () {
    [$status, $output] = run('default', [
        '--duplicate' => true,
        '--config' => base_path('tests/Fixtures/test-j/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '6 files, New 38 translations scanned');

    expect(getContent(base_path('tests/Fixtures/test-j/module1/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description!' => 'App. Description!',
        'App. Name.' => 'App. Name.',
        'Base Description' => 'Base Description',
        'Base Name' => 'Base Name',
        'Base Version' => 'Base Version',
        'Base. Description!' => 'Base. Description!',
        'Base. Name.' => 'Base. Name!',
        'app' => [
            'description' => 'app.description',
            'name' => 'app.name',
        ],
        'base' => [
            'description' => 'base.description',
            'name' => 'base.name',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module1/lang/pt-BR.json')))->toBe([
        'App Description' => 'Descrição App',
        'App Name' => 'Nome App',
        'App Version' => 'Versão App',
        'App. Description!' => 'App. Descrição!',
        'App. Name.' => 'App. Nome.',
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Description!' => 'Base. Descrição!',
        'Base. Name.' => 'Base. Nome.',
        'app' => [
            'description' => 'app.descrição',
            'name' => 'app.nome',
        ],
        'base' => [
            'description' => 'base.descrição',
            'name' => 'base.nome',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module2/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description.' => 'App. Description.',
        'App. Name!' => 'App. Name!',
        'App. Version!' => 'App. Version!',
        'Base Description' => 'Base Description',
        'Base Name' => 'Base Name',
        'Base Version' => 'Base Version',
        'Base. Description.' => 'Base. Description.',
        'Base. Name!' => 'Base. Name!',
        'app' => [
            'description' => 'app.description',
            'version' => 'app.version',
        ],
        'base' => [
            'name' => 'base.name',
            'version' => 'base.version',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module2/lang/pt-BR.json')))->toBe([
        'App Description' => 'Descrição App',
        'App Name' => 'Nome App',
        'App Version' => 'Versão App',
        'App. Description.' => 'App. Descrição.',
        'App. Name!' => 'App. Nome!',
        'App. Version!' => 'App. Versão!',
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Description.' => 'Base. Descrição.',
        'Base. Name!' => 'Base. Nome!',
        'app' => [
            'description' => 'app.descrição',
            'version' => 'app.versão',
        ],
        'base' => [
            'name' => 'base.nome',
            'version' => 'base.versão',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module3/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description!' => 'App. Description!',
        'App. Version.' => 'App. Version.',
        'Base Description' => 'Base Description',
        'Base Name' => 'Base Name',
        'Base Version' => 'Base Version',
        'Base. Version!' => 'Base. Version!',
        'app' => [
            'name' => 'app.name',
            'version' => 'app.version',
        ],
        'base' => [
            'description' => 'base.description',
            'version' => 'base.version',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module3/lang/pt-BR.json')))->toBe([
        'App Description' => 'Descrição App',
        'App Name' => 'Nome App',
        'App Version' => 'Versão App',
        'App. Description!' => 'App. Descrição!',
        'App. Version.' => 'App. Versão.',
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Version!' => 'Base. Versão!',
        'app' => [
            'name' => 'app.nome',
            'version' => 'app.versão',
        ],
        'base' => [
            'description' => 'base.descrição',
            'version' => 'base.versão',
        ],
    ]);
});

test('it should run default command (test-j) removing duplicates', function () {
    [$status, $output] = run('default', [
        '--remove' => true,
        '--duplicate' => true,
        '--config' => base_path('tests/Fixtures/test-j/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '6 files, New 38 translations scanned');

    expect(getContent(base_path('tests/Fixtures/test-j/module1/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description!' => 'App. Description!',
        'App. Name.' => 'App. Name.',
        'Base Description' => 'Base Description',
        'Base Name' => 'Base Name',
        'Base Version' => 'Base Version',
        'Base. Description!' => 'Base. Description!',
        'Base. Name.' => 'Base. Name!',
        'app' => [
            'description' => 'app.description',
            'name' => 'app.name',
        ],
        'base' => [
            'description' => 'base.description',
            'name' => 'base.name',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module1/lang/pt-BR.json')))->toBe([
        'App Description' => 'Descrição App',
        'App Name' => 'Nome App',
        'App Version' => 'Versão App',
        'App. Description!' => 'App. Descrição!',
        'App. Name.' => 'App. Nome.',
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Description!' => 'Base. Descrição!',
        'Base. Name.' => 'Base. Nome.',
        'app' => [
            'description' => 'app.descrição',
            'name' => 'app.nome',
        ],
        'base' => [
            'description' => 'base.descrição',
            'name' => 'base.nome',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module2/lang/en-US.json')))->toBe([
        'App. Description.' => 'App. Description.',
        'App. Name!' => 'App. Name!',
        'App. Version!' => 'App. Version!',
        'Base. Description.' => 'Base. Description.',
        'Base. Name!' => 'Base. Name!',
        'app' => [
            'version' => 'app.version',
        ],
        'base' => [
            'version' => 'base.version',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module2/lang/pt-BR.json')))->toBe([
        'App. Description.' => 'App. Descrição.',
        'App. Name!' => 'App. Nome!',
        'App. Version!' => 'App. Versão!',
        'Base. Description.' => 'Base. Descrição.',
        'Base. Name!' => 'Base. Nome!',
        'app' => [
            'version' => 'app.versão',
        ],
        'base' => [
            'version' => 'base.versão',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module3/lang/en-US.json')))->toBe([
        'App. Version.' => 'App. Version.',
        'Base. Version!' => 'Base. Version!',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-j/module3/lang/pt-BR.json')))->toBe([
        'App. Version.' => 'App. Versão.',
        'Base. Version!' => 'Base. Versão!',
    ]);
});
