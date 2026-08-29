# Troubleshooting

## Theme CSS missing or stale in dev

1. Confirm `symfinity/ui-kernel` is registered in `config/bundles.php`.
2. After editing DTCG theme files under `config/themes/`, run `bin/console cache:clear`.
3. In dev, remove compiled AssetMapper output under `public/assets/` if it shadows live kernel CSS.

## Wrong colours or variants after upgrade

Follow the version sections in [upgrade.md](upgrade.md). Schema **2.0** semantic colours and physics axis changes in **0.2.x** require theme file updates and CSS snapshot regeneration.

## Web Profiler panel missing

The UI Kernel collector registers only when `kernel.debug` is true and `symfony/web-profiler-bundle` is installed. See [profiler.md](profiler.md).
