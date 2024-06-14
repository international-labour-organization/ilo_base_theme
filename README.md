# ILO Base Theme

Drupal 10 theme based on the [ILO Design System][1]. The project is structured in the following way:

- A very basic base theme, with not much in it at the moment. This will change in the future as more and more default
  Drupal templates (e.g. tabs, forms, etc.) will be provided by default, complying with the ILO Design System.
- A theme companion module [`ilo_base_theme_companion`](modules/ilo_base_theme_companion), which exposes all compatible
  ILO Design System components as Drupal patterns. Check the [UI Patterns][6] and the [UI Patterns Settings][7] modules for more information.

The theme requires the companion module to be enabled, while one could enable the companion module without enabling the base theme.

## Table of contents

- [Installation](#installation)
  - [Use the base theme](#use-the-base-theme)
  - [Use the components without the base theme](#use-the-components-without-the-base-theme)
- [Run the demo site locally](#run-the-demo-site-locally)
- [Run the demo site as a Docker service](#run-the-demo-site-as-a-docker-service)
- [Development](#development)

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

### Patterns and cache metadata

Displaying render arrays using patterns requires a careful handing of the render array's cache metadata. For example,
if you want to use the `card` pattern to render a news content type teaser, you would typically do the following:

```twig
{{ pattern('card', {
  title: content.title,
  link: content.field_link['#url'],
  size: 'fluid',
}, 'feature') }}
```

The problem with the above is that cache tags and contexts (for example from the link at `field_link`) are not bubbled up correctly.

In order to solve the issue it is recommended to explicitly bubble up the cache metadata of the render array at hand.
You can do that by using the `|cache_metadata` filter exposed by the [Twig Tweak][9] module, as shown below:

```twig
{{ pattern('card', {
  title: content.title,
  link: content.field_link['#url'],
  size: 'fluid',
}, 'feature') }}

{{ content|cache_metadata }}
```

Another recommended module to keep in mind, when working with patterns, is the [Twig Field Value][10], which can help with
accessing properties and subfields of render arrays and entities when passing them over to patterns.

## Run the demo site locally

This project also ships with a buildable demo site, which allows developers to preview the base theme with ease. To do so run:

```
make
```

This will build a fully functional Drupal site with the ILO Base Theme enabled by default. After the installation is done visit:

[http://localhost:8080](http://localhost:8080)

Note: the command above builds a demo site as self-contained service. To do so it performs the following commands in the container:

- Run `npm install` to fetch the ILO Design System assets
- Copy all relevant Drupal-related code in `/opt/drupal`
- Build and install the Drupal site

This means that, when fetching a newer version, you might need to rebuild the demo site from scratch. To do so, run:

```
make build-dist
```

## Run the demo site as a Docker service

The demo site is also published in the GitHub Docker registry. To run the site use the following command:

```
docker run -p 8082:80 ghcr.io/international-labour-organization/ilo_base_theme:0.x
```

The site will then be available at http://localhost:8082.

In order to run the command above, you need to be authenticated, please check the related [documentation][8].

## Render patterns on demand

The [`ilo_base_theme_test`](modules/ilo_base_theme_test) module exposes a `/pattern-preview?id=...&fields=...` route
that allows users to render a pattern on demand, by passing its ID and its fields as an encoded JSON object.

For example, to render a button, one could pass the following fields as JSON:

```json
{
  "label": "Button",
  "type": "primary",
  "kind": "button",
  "size": "medium"
}
```

Encoded, that will look like the following:

```
http://localhost:8081/pattern-preview?id=button&fields=%7b%0a%22label%22%3a%20%22Button%22%2c%0a%22type%22%3a%20%22primary%22%2c%0a%22kind%22%3a%20%22button%22%2c%0a%22size%22%3a%20%22medium%22%0a%7d
```

The test module above is enabled by default in both `dev` and `dist` Docker images but it is not included in the released package.

## Development

The project contains all the necessary code and tools for an effective development process, meaning:

- All PHP development dependencies (Drupal core included) are required in [composer.json](composer.json)
- All Node.js development dependencies are required in [package.json](package.json)
- Project setup and installation can be easily handled thanks to the integration with the [Task Runner][2] project.
- All system requirements are containerized using [Docker Compose][3].

Development can be set up via [Makefile](Makefile)'s targets, as follows:

- Install Node dependencies by running `npm install`.
- Start the development environment by running `make up-dev install`. This will:
  - Build the development Drupal container from the `dev` target of the shipped [Dockerfile](Dockerfile)
  - Build a Drupal target site within the project
  - Symlink the base theme codebase within the target site
  - Mount the target site within the dev container
  - Install the target site
  - Expose the site on [http://localhost:8081](http://localhost:8081)

When working on the theme you might want to enable Drupal Twig debugging by running:

```
make twig-debug-on
```

[1]: https://github.com/international-labour-organization/designsystem
[2]: https://github.com/openeuropa/task-runner
[3]: https://docs.docker.com/compose
[4]: https://getcomposer.org/
[5]: https://github.com/openeuropa/composer-artifacts
[6]: https://www.drupal.org/project/ui_patterns
[7]: https://www.drupal.org/project/ui_patterns_settings
[8]: https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry#authenticating-with-a-personal-access-token-classic
[9]: https://www.drupal.org/project/twig_tweak
[10]: https://www.drupal.org/project/twig_field_value
