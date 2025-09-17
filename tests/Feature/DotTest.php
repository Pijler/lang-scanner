<?php

test('it should run default command (test-a)', function () {
    [$status, $output] = run('default', [
        '--dot' => true,
        '--config' => base_path('tests/Fixtures/test-a/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '2 files, New 48 translations scanned');

    expect(getContent(base_path('tests/Fixtures/test-a/lang/en-US.json')))->toBe([
        'App Description' => '',
        'App Name' => '',
        'App Version' => '',
        'App. Description!' => '',
        'App. Description.' => '',
        'App. Name!' => '',
        'App. Name.' => '',
        'App. Version!' => '',
        'App. Version.' => '',
        'Base Description' => '',
        'Base Name' => '',
        'Base Version' => '',
        'Base. Description!' => '',
        'Base. Description.' => '',
        'Base. Name!' => '',
        'Base. Name.' => '',
        'Base. Version!' => '',
        'Base. Version.' => '',
        'app.description' => '',
        'app.name' => '',
        'app.version' => '',
        'base.description' => '',
        'base.name' => '',
        'base.version' => '',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-a/lang/pt-BR.json')))->toBe([
        'App Description' => '',
        'App Name' => '',
        'App Version' => '',
        'App. Description!' => '',
        'App. Description.' => '',
        'App. Name!' => '',
        'App. Name.' => '',
        'App. Version!' => '',
        'App. Version.' => '',
        'Base Description' => '',
        'Base Name' => '',
        'Base Version' => '',
        'Base. Description!' => '',
        'Base. Description.' => '',
        'Base. Name!' => '',
        'Base. Name.' => '',
        'Base. Version!' => '',
        'Base. Version.' => '',
        'app.description' => '',
        'app.name' => '',
        'app.version' => '',
        'base.description' => '',
        'base.name' => '',
        'base.version' => '',
    ]);
});

test('it should run default command (test-b)', function () {
    [$status, $output] = run('default', [
        '--dot' => true,
        '--config' => base_path('tests/Fixtures/test-b/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '2 files, New 24 translations scanned');

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
        'Base Description' => '',
        'Base Name' => '',
        'Base Version' => '',
        'Base. Description!' => '',
        'Base. Description.' => '',
        'Base. Name!' => '',
        'Base. Name.' => '',
        'Base. Version!' => '',
        'Base. Version.' => '',
        'app.description' => 'app.description',
        'app.name' => 'app.name',
        'app.version' => 'app.version',
        'base.description' => '',
        'base.name' => '',
        'base.version' => '',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-b/lang/pt-BR.json')))->toBe([
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
        'app.description' => '',
        'app.name' => '',
        'app.version' => '',
        'base.description' => 'base.descrição',
        'base.name' => 'base.nome',
        'base.version' => 'base.versão',
    ]);
});

test('it should run default command (test-c)', function () {
    [$status, $output] = run('default', [
        '--dot' => true,
        '--config' => base_path('tests/Fixtures/test-c/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('NO ISSUES', '2 files');

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
        'app.description' => 'app.description',
        'app.name' => 'app.name',
        'app.version' => 'app.version',
        'base.description' => '',
        'base.name' => '',
        'base.version' => '',
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
        'app.description' => '',
        'app.name' => '',
        'app.version' => '',
        'base.description' => 'base.descrição',
        'base.name' => 'base.nome',
        'base.version' => 'base.versão',
    ]);
});

test('it should run default command (test-d)', function () {
    [$status, $output] = run('default', [
        '--dot' => true,
        '--config' => base_path('tests/Fixtures/test-d/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('NO ISSUES', '2 files');

    expect(getContent(base_path('tests/Fixtures/test-d/lang/en-US.json')))->toBe([
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
        'app.description' => 'app.description',
        'app.name' => 'app.name',
        'app.version' => 'app.version',
        'base.description' => 'base.description',
        'base.name' => 'base.name',
        'base.version' => 'base.version',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-d/lang/pt-BR.json')))->toBe([
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
        'app.description' => 'app.descrição',
        'app.name' => 'app.nome',
        'app.version' => 'app.versão',
        'base.description' => 'base.descrição',
        'base.name' => 'base.nome',
        'base.version' => 'base.versão',
    ]);
});

test('it should run default command (test-e)', function () {
    [$status, $output] = run('default', [
        '--dot' => true,
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
        'app.description' => 'app.description',
        'app.name' => 'app.name',
        'app.version' => 'app.version',
        'base.description' => 'base.description',
        'base.name' => 'base.name',
        'base.version' => 'base.version',
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
        'app.description' => 'app.descrição',
        'app.name' => 'app.nome',
        'app.version' => 'app.versão',
        'base.description' => 'base.descrição',
        'base.name' => 'base.nome',
        'base.version' => 'base.versão',
    ]);
});
