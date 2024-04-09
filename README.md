# ILO Base Theme

Drupal 10 theme based on the [ILO Design System][1]. The project is structured in the following way:

- A very basic base theme, with not much in it at the moment. This will change in the future as more and more default
  Drupal templates (e.g. tabs, forms, etc.) will be provided by default, complying with the ILO Design System.
- A theme companion module [`ilo_base_theme_companion`](modules/ilo_base_theme_companion), which exposes all compatible
  ILO Design System components as Drupal patterns. Check the [UI Patterns][6] and the [UI Patterns Settings][7] modules for more information.

The theme requires the companion module to be enabled, while one could enable the companion module without enabling the base theme.

This project also ships with a buildable demo site, which allows developers to preview the base theme with ease. To do so run:

```
make
```

This will build a fully functional Drupal site with the ILO Base Theme enabled by default. After the installation is done visit:

[http://localhost:8080](http://localhost:8080)

## Installation

The recommended way of installing the ILO Base Theme is via [Composer][4].

Before proceeding, please note that theme releases are built by a continuous integration system, and include code coming
from third-party libraries, such as [ILO Design System][1] templates and other assets. Simply Running `composer require international-labour-organization/ilo_base_theme`
will download the raw theme source code, which misses required third-party code.

In order to instruct Composer to download the actual built artifact, you need to require and configure the
[Composer Artifacts][5] project.

To do so, run:

```
composer require openeuropa/composer-artifacts
```

Then add the following section in your project's `composer.json`:

```
    "extra": {
        "artifacts": {
            "international-labour-organization/ilo_base_theme": {
                "dist": {
                    "url": "https://github.com/{name}/releases/download/{pretty-version}/{project-name}-{pretty-version}.zip",
                    "type": "zip"
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

### Use the base theme

In order to enable the theme in your project perform the following steps:

1. Enable the **ILO base theme companion** module
2. Enable the **ILO base theme** and set it as default

```
./vendor/bin/drush en ilo_base_theme_companion
./vendor/bin/drush theme:enable ilo_base_theme
./vendor/bin/drush config-set system.theme default ilo_base_theme
```

### Use the components without the base theme

If you already have a theme, and you just want to use the design system components, just enable the companion module,
without enabling the theme, like so:

```
./vendor/bin/drush en ilo_base_theme_companion
```

The full list of components is available at `/patterns`.

## Development

This contains all the necessary code and tools for an effective development process, meaning:

- All PHP development dependencies (Drupal core included) are required in [composer.json](composer.json)
- All Node.js development dependencies are required in [package.json](package.json)
- Project setup and installation can be easily handled thanks to the integration with the [Task Runner][2] project.
- All system requirements are containerized using [Docker Compose][3].

Development can be set up via [Makefile](Makefile)'s targets, as follows:

- Install Node dependencies by running `npm install`.
- Start the development environment by running `make up-dev`. This will:
  - Build the development Drupal container from the `dev` target of the shipped [Dockerfile](Dockerfile)
  - Build a Drupal target site within the project
  - Symlink the base theme codebase within the target site
  - Mount the target site within the dev container
  - Install the target site
  - Expose the site on [http://localhost:8081](http://localhost:8081)

[1]: https://github.com/international-labour-organization/designsystem
[2]: https://github.com/openeuropa/task-runner
[3]: https://docs.docker.com/compose
[4]: https://getcomposer.org/
[5]: https://github.com/openeuropa/composer-artifacts
[6]: https://www.drupal.org/project/ui_patterns
[7]: https://www.drupal.org/project/ui_patterns_settings
