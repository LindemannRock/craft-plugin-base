# Console help @since(5.26.0)

Plugin-level console help gives operators a predictable discovery command:

```bash title="PHP"
php craft my-plugin/help [command]
```

```bash title="DDEV"
ddev craft my-plugin/help [command]
```

Omit `[command]` for the catalog, or pass a path such as `maintenance/clean-by-type` for focused help. Craft/Yii already support exact-command help with `php craft help my-plugin/group/action`, but users need to know the exact group and action first. The base console help pattern fills that gap with a concise plugin catalog and focused command pages.

## Controller

Add a `HelpController` in the plugin's console namespace and extend the base controller:

```php
namespace vendor\myplugin\console\controllers;

use lindemannrock\base\console\controllers\AbstractHelpController;

final class HelpController extends AbstractHelpController
{
    protected function helpManifest(): array
    {
        return [
            'title' => 'My Plugin',
            'pluginHandle' => 'my-plugin',
            'commandPrefixes' => [
                'php craft',
                'ddev craft',
            ],
            'summary' => 'Manage plugin maintenance tasks from the command line.',
            'common' => [
                'maintenance/clean-unused',
            ],
            'groups' => [
                [
                    'name' => 'maintenance',
                    'label' => 'Maintenance',
                    'description' => 'Scan and clean plugin data.',
                    'commands' => [
                        [
                            'path' => 'maintenance/clean-unused',
                            'summary' => 'Delete unused rows.',
                            'description' => 'Delete rows that have already been marked unused.',
                            'examples' => [
                                'my-plugin/maintenance/clean-unused',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
```

The plugin still needs to route console requests to its console namespace:

```php
if (Craft::$app instanceof \craft\console\Application) {
    $this->controllerNamespace = 'vendor\myplugin\console\controllers';
}
```

## Manifest fields

Top-level fields:

| Field | Type | Description |
|-------|------|-------------|
| `title` | `string` | Display name used in the help header |
| `pluginHandle` | `string` | Craft plugin handle, used to build command paths |
| `commandPrefixes` | `string[]` | Prefixes used in examples, usually `php craft` and `ddev craft` |
| `commandPrefix` | `string` | Legacy single-prefix fallback when `commandPrefixes` is omitted |
| `summary` | `string` | Short operator-facing explanation |
| `common` | `string[]` | Command paths to highlight first |
| `groups` | `array[]` | Group sections such as `translations`, `maintenance`, or `backup` |

Group fields:

| Field | Type | Description |
|-------|------|-------------|
| `name` | `string` | Console controller segment |
| `label` | `string` | Human label for the section |
| `description` | `string` | One-line group explanation |
| `commands` | `array[]` | Command entries |

Command fields:

| Field | Type | Description |
|-------|------|-------------|
| `path` | `string` | Command path without the plugin handle |
| `summary` | `string` | One-line catalog summary |
| `description` | `string` | Focused help description |
| `arguments` | `string` | Positional arguments shown in usage |
| `usageOptions` | `string` | Options shown in usage |
| `options` | `array[]` | Option descriptions |
| `examples` | `string[]` | Full command paths without the prefix |
| `notes` | `string[]` | Short warnings or behavior notes |

Option entries use:

```php
[
    'name' => '--type',
    'description' => 'all, site, or forms.',
    'required' => true,
]
```

## Output guidelines

Keep the top-level catalog brief:

- Explain what the CLI surface is for in one sentence.
- Highlight 3-5 common commands.
- Group everything else by task area.
- Show how to get focused help.

Focused command help should include:

- The exact command path.
- Required arguments and options.
- Valid option values when they are constrained.
- One or two examples that can be copied directly.
- Notes only when they prevent mistakes.

Avoid dumping internal class names, service names, or every implementation detail. The help command is for operators first; developer reference can stay in docs.

## Unknown commands

When a user asks for a command that is not in the manifest, the helper suggests the closest known command. This is useful when the action name is right but the group is wrong:

```bash title="PHP"
php craft my-plugin/help translations/clean-by-type
```

```bash title="DDEV"
ddev craft my-plugin/help translations/clean-by-type
```

Can suggest:

```text title="Suggested commands"
php craft my-plugin/help maintenance/clean-by-type
php craft my-plugin/maintenance/clean-by-type
```

## Related

- [Bootstrapping](../developers/bootstrapping.md) — registering console namespaces
- [API Reference](../developers/api-reference.md) — public classes and methods
