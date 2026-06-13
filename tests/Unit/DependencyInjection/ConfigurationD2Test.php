<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationD2Test extends TestCase
{
    #[Test]
    public function appContractOverrideIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('symfinity_ui_kernel.contract');

        $this->process([
            'contract' => [
                'palette' => [
                    'hues' => ['blue'],
                ],
            ],
        ]);
    }

    #[Test]
    public function appGeneratorOverrideIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('symfinity_ui_kernel.generator');

        $this->process([
            'generator' => [
                'palette' => [
                    'interpolation' => 'oklch',
                ],
            ],
        ]);
    }

    #[Test]
    public function allowedAppOptionsStillProcess(): void
    {
        $config = $this->process([
            'default_theme' => 'semantic',
            'default_variant' => 'semantic',
            'user_tokens' => [
                '--ui-color-primary' => '#336699',
            ],
        ]);

        self::assertSame('semantic', $config['default_theme']);
        self::assertSame('semantic', $config['default_variant']);
        /** @var array<string, string> $userTokens */
        $userTokens = $config['user_tokens'];
        self::assertSame('#336699', $userTokens['--ui-color-primary']);
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    private function process(array $input): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$input]);
    }
}
