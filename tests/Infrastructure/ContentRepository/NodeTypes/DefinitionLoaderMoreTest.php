<?php

declare(strict_types=1);

namespace Aurora\Tests\Infrastructure\ContentRepository\NodeTypes;

use Aurora\Infrastructure\ContentRepository\NodeTypes\DefinitionLoader;
use Aurora\Infrastructure\ContentRepository\NodeTypes\NodeTypeResolver;
use PHPUnit\Framework\TestCase;

final class DefinitionLoaderMoreTest extends TestCase
{
    public function testDefaultPathLoadsExhaustiveExample(): void
    {
        $loader = new DefinitionLoader();
        $defs = $loader->load();
        // from config/node_types/exhaustive-example.yaml added to the repo
        $this->assertArrayHasKey('exhaustive-example', $defs);
        $this->assertArrayHasKey('exhaustive-base', $defs);
    }

    public function testUppercaseExtensionIsRecognized(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_defs_upper_'.bin2hex(random_bytes(3));
        @mkdir($dir);
        $yaml = "typeupper:\n  properties:\n    title: { type: string }\n";
        file_put_contents($dir.DIRECTORY_SEPARATOR.'def.UPPER.YAML', $yaml);

        $loader = new DefinitionLoader([$dir]);
        $defs = $loader->load();
        $this->assertArrayHasKey('typeupper', $defs);

        @unlink($dir.DIRECTORY_SEPARATOR.'def.UPPER.YAML');
        @rmdir($dir);
    }

    public function testCastsNullableAndMultipleFromJson(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aurora_defs_json_'.bin2hex(random_bytes(3));
        @mkdir($dir);
        $json = json_encode([
            'article' => [
                'properties' => [
                    'title' => ['type' => 'string'],
                    'flag' => ['type' => 'bool', 'nullable' => 1, 'multiple' => 0],
                ],
            ],
        ], JSON_PRETTY_PRINT);
        file_put_contents($dir.DIRECTORY_SEPARATOR.'defs.json', (string) $json);

        $loader = new DefinitionLoader([$dir]);
        $resolver = new NodeTypeResolver();
        $resolved = $resolver->resolve($loader->load());

        $t = $resolved['article'];
        $rp = new \ReflectionProperty(\Aurora\Domain\ContentRepository\Type\NodeType::class, 'definitions');
        $rp->setAccessible(true);
        /** @var array<string, \Aurora\Domain\ContentRepository\Type\PropertyDefinition> $defsMap */
        $defsMap = $rp->getValue($t);
        $this->assertFalse($defsMap['title']->nullable);
        $this->assertFalse($defsMap['title']->multiple);
        $this->assertTrue($defsMap['flag']->nullable);
        $this->assertFalse($defsMap['flag']->multiple);

        @unlink($dir.DIRECTORY_SEPARATOR.'defs.json');
        @rmdir($dir);
    }
}

