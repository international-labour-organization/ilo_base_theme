# ILO Base Theme

Drupal 10 theme based on the [ILO Design System][1].

This projects ships with a buildable demo site, which allows developers to preview the base theme with ease. To so so run:

```
make
```

This will build a fully functional Drupal site with the ILO Base Theme enabled by default. After the installation is done visit:

[http://localhost:8080/build](http://localhost:8080/build).

## Installation

The recommended way of installing the OpenEuropa theme is via [Composer][2].

Before proceeding, please note that theme releases are built by a continuous integration system, and include code coming
from third-party libraries, such as [ILO Design System][1] templates and other assets. Simply Running `composer require openeuropa/oe_theme`
will download the raw theme source code, which misses required third-party code.

In order to instruct Composer to download the actual built artifact, you need to require and configure the
[Composer Artifacts][19] project.

To do so, run:

```
composer require openeuropa/composer-artifacts
```

Then add the following section, in your project's `composer.json`:

```
    "extra": {
        "artifacts": {
            "international-labour-organization/ilo_base_theme": {
                "dist": {
                    "url": "https://github.com/{name}/releases/download/{pretty-version}/{project-name}-{pretty-version}.zip",
                    "type": "tar"
                }
            }
        },
    }
```

Once you are done, run:

```bash
composer require international-labour-organization/ilo_base_theme
```

This will download the fully built artifact, as opposed to the raw theme source code.

### Enable the theme

In order to enable the theme in your project perform the following steps:

1. Enable the **ILO base theme companion** module ```./vendor/bin/drush en ilo_base_theme_companion```
2. Enable the **ILO base theme** and set it as default

```
./vendor/bin/drush en ilo_base_theme_companion
./vendor/bin/drush theme:enable ilo_base_theme
./vendor/bin/drush config-set system.theme default ilo_base_theme
```

If you already have a theme, and you just want to use the design system components, just enable the companion module,
without enabling the theme, like so:

```
./vendor/bin/drush en ilo_base_theme_companion
```

## Development

This contains all the necessary code and tools for an effective development process, meaning:

- All PHP development dependencies (Drupal core included) are required in [composer.json](composer.json)
- All Node.js development dependencies are required in [package.json](package.json)
- Project setup and installation can be easily handled thanks to the integration with the [Task Runner][2] project.
- All system requirements are containerized using [Docker Compose][3].


[1]: https://github.com/international-labour-organization/designsystem
[2]: https://github.com/openeuropa/task-runner
[3]: https://docs.docker.com/compose
