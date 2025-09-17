<?php

test('it should run default command (test-a)', function () {
    [$status, $output] = run('default', [
        '--check' => true,
        '--no-empty' => true,
        '--config' => base_path('tests/Fixtures/test-a/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('NO ISSUES', '2 files');

    expect(getContent(base_path('tests/Fixtures/test-a/lang/en-US.json')))->toBe([]);

    expect(getContent(base_path('tests/Fixtures/test-a/lang/pt-BR.json')))->toBe([]);
});

test('it should run default command (test-b)', function () {
    [$status, $output] = run('default', [
        '--check' => true,
        '--no-empty' => true,
        '--config' => base_path('tests/Fixtures/test-b/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('FAIL', '2 files, Missing 24 translations');

    expect(getContent(base_path('tests/Fixtures/test-b/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description!' => 'App. Description!',
        'App. Description.' => 'App. Description.',
        'App. Name!' => 'App. Name!',
        'App. Name.' => 'App. Name.',
        'App. Version!' => 'App. Version!',
        'App. Version.' => 'App. Version.',
        'app' => [
            'description' => 'app.description',
            'name' => 'app.name',
            'version' => 'app.version',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-b/lang/pt-BR.json')))->toBe([
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Description!' => 'Base. Descrição!',
        'Base. Description.' => 'Base. Descrição.',
        'Base. Name!' => 'Base. Nome!',
        'Base. Name.' => 'Base. Nome.',
        'Base. Version!' => 'Base. Versão!',
        'Base. Version.' => 'Base. Versão.',
        'base' => [
            'description' => 'base.descrição',
            'name' => 'base.nome',
            'version' => 'base.versão',
        ],
    ]);
});

test('it should run default command (test-c)', function () {
    [$status, $output] = run('default', [
        '--check' => true,
        '--no-empty' => true,
        '--config' => base_path('tests/Fixtures/test-c/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('FAIL', '2 files, Missing 24 translations');

    expect(getContent(base_path('tests/Fixtures/test-c/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description!' => 'App. Description!',
        'App. Description.' => 'App. Description.',
        'App. Name!' => 'App. Name!',
        'App. Name.' => 'App. Name.',
        'App. Version!' => 'App. Version!',
        'App. Version.' => 'App. Version.',
        'Base Description' => '',
        'Base Name' => '',
        'Base Version' => '',
        'Base. Description!' => '',
        'Base. Description.' => '',
        'Base. Name!' => '',
        'Base. Name.' => '',
        'Base. Version!' => '',
        'Base. Version.' => '',
        'app' => [
            'description' => 'app.description',
            'name' => 'app.name',
            'version' => 'app.version',
        ],
        'base' => [
            'description' => '',
            'name' => '',
            'version' => '',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-c/lang/pt-BR.json')))->toBe([
        'App Description' => '',
        'App Name' => '',
        'App Version' => '',
        'App. Description!' => '',
        'App. Description.' => '',
        'App. Name!' => '',
        'App. Name.' => '',
        'App. Version!' => '',
        'App. Version.' => '',
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Description!' => 'Base. Descrição!',
        'Base. Description.' => 'Base. Descrição.',
        'Base. Name!' => 'Base. Nome!',
        'Base. Name.' => 'Base. Nome.',
        'Base. Version!' => 'Base. Versão!',
        'Base. Version.' => 'Base. Versão.',
        'app' => [
            'description' => '',
            'name' => '',
            'version' => '',
        ],
        'base' => [
            'description' => 'base.descrição',
            'name' => 'base.nome',
            'version' => 'base.versão',
        ],
    ]);
});

test('it should run default command (test-d)', function () {
    [$status, $output] = run('default', [
        '--sort' => false,
        '--check' => true,
        '--no-empty' => true,
        '--config' => base_path('tests/Fixtures/test-d/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('NO ISSUES', '2 files');

    expect(getContent(base_path('tests/Fixtures/test-d/lang/en-US.json')))->toBe([
        'App Name' => 'App Name',
        'Base. Description.' => 'Base. Description.',
        'App. Description!' => 'App. Description!',
        'Base. Version.' => 'Base. Version.',
        'App. Description.' => 'App. Description.',
        'App. Name!' => 'App. Name!',
        'Base Name' => 'Base Name',
        'App. Version!' => 'App. Version!',
        'App. Version.' => 'App. Version.',
        'Base. Description!' => 'Base. Description!',
        'Base Description' => 'Base Description',
        'App Version' => 'App Version',
        'Base. Name!' => 'Base. Name!',
        'App. Name.' => 'App. Name.',
        'Base. Name.' => 'Base. Name.',
        'Base. Version!' => 'Base. Version!',
        'App Description' => 'App Description',
        'Base Version' => 'Base Version',
        'app' => [
            'name' => 'app.name',
            'version' => 'app.version',
            'description' => 'app.description',
        ],
        'base' => [
            'version' => 'base.version',
            'description' => 'base.description',
            'name' => 'base.name',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-d/lang/pt-BR.json')))->toBe([
        'App Description' => 'Descrição App',
        'App Version' => 'Versão App',
        'base' => [
            'name' => 'base.nome',
            'version' => 'base.versão',
            'description' => 'base.descrição',
        ],
        'Base. Name.' => 'Base. Nome.',
        'App. Description.' => 'App. Descrição.',
        'App. Description!' => 'App. Descrição!',
        'App. Name.' => 'App. Nome.',
        'Base Name' => 'Nome Base',
        'app' => [
            'name' => 'app.nome',
            'description' => 'app.descrição',
            'version' => 'app.versão',
        ],
        'App. Version!' => 'App. Versão!',
        'Base Version' => 'Versão Base',
        'App. Version.' => 'App. Versão.',
        'Base Description' => 'Descrição Base',
        'Base. Description!' => 'Base. Descrição!',
        'App. Name!' => 'App. Nome!',
        'Base. Description.' => 'Base. Descrição.',
        'Base. Name!' => 'Base. Nome!',
        'Base. Version!' => 'Base. Versão!',
        'App Name' => 'Nome App',
        'Base. Version.' => 'Base. Versão.',
    ]);
});

test('it should run default command (test-e)', function () {
    [$status, $output] = run('default', [
        '--check' => true,
        '--no-empty' => true,
        '--config' => base_path('tests/Fixtures/test-e/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('NO ISSUES', '2 files');

    expect(getContent(base_path('tests/Fixtures/test-e/lang/en-US.json')))->toBe([
        'App Description' => 'App Description',
        'App Name' => 'App Name',
        'App Version' => 'App Version',
        'App. Description!' => 'App. Description!',
        'App. Description.' => 'App. Description.',
        'App. Name!' => 'App. Name!',
        'App. Name.' => 'App. Name.',
        'App. Version!' => 'App. Version!',
        'App. Version.' => 'App. Version.',
        'Base Description' => 'Base Description',
        'Base Name' => 'Base Name',
        'Base Version' => 'Base Version',
        'Base. Description!' => 'Base. Description!',
        'Base. Description.' => 'Base. Description.',
        'Base. Name!' => 'Base. Name!',
        'Base. Name.' => 'Base. Name.',
        'Base. Version!' => 'Base. Version!',
        'Base. Version.' => 'Base. Version.',
        'app' => [
            'description' => 'app.description',
            'name' => 'app.name',
            'version' => 'app.version',
        ],
        'base' => [
            'description' => 'base.description',
            'name' => 'base.name',
            'version' => 'base.version',
        ],
    ]);

    expect(getContent(base_path('tests/Fixtures/test-e/lang/pt-BR.json')))->toBe([
        'App Description' => 'Descrição App',
        'App Name' => 'Nome App',
        'App Version' => 'Versão App',
        'App. Description!' => 'App. Descrição!',
        'App. Description.' => 'App. Descrição.',
        'App. Name!' => 'App. Nome!',
        'App. Name.' => 'App. Nome.',
        'App. Version!' => 'App. Versão!',
        'App. Version.' => 'App. Versão.',
        'Base Description' => 'Descrição Base',
        'Base Name' => 'Nome Base',
        'Base Version' => 'Versão Base',
        'Base. Description!' => 'Base. Descrição!',
        'Base. Description.' => 'Base. Descrição.',
        'Base. Name!' => 'Base. Nome!',
        'Base. Name.' => 'Base. Nome.',
        'Base. Version!' => 'Base. Versão!',
        'Base. Version.' => 'Base. Versão.',
        'app' => [
            'description' => 'app.descrição',
            'name' => 'app.nome',
            'version' => 'app.versão',
        ],
        'base' => [
            'description' => 'base.descrição',
            'name' => 'base.nome',
            'version' => 'base.versão',
        ],
    ]);
});
