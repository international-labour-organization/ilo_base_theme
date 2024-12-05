# Development

The project contains all the necessary code and tools for an effective development process, meaning:

- All PHP development dependencies (Drupal core included) are required in [composer.json](composer.json)
- All Node.js development dependencies are required in [package.json](package.json)
- Project setup and installation can be easily handled thanks to the integration with the [Task Runner][1] project.
- All system requirements are containerized using [Docker Compose][2].

Development can be set up via [Makefile](Makefile)'s targets, as follows:

- Start the development environment by running `make up-dev install`. This will:
  - Build the development Drupal container from the `dev` target of the shipped [Dockerfile](Dockerfile)
  - Build a Drupal target site within the project
  - Symlink the base theme codebase within the target site
  - Mount the target site within the dev container
  - Install the target site
  - Install NodeJS dependencies by running `npm install` in the node container.
  - Install the Design System
  - Build a production ready version of the Drupal Theme
  - Expose the site on [http://localhost:8081](http://localhost:8081)

**Please note:** project files and directories are symlinked within the target site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need run:

```
$ ./vendor/bin/run drupal:symlink-project
```

When working on the theme you might want to enable Drupal Twig debugging by running:

```
make twig-debug-on
```

## Render patterns on demand

The [`ilo_base_theme_preview`](modules/ilo_base_theme_preview) module exposes a `/pattern-preview?id=...&variant=...&fields=...` route
that allows users to render a pattern on demand, by passing its ID, its variant (optional) and its fields as an encoded JSON object.

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

Pattern settings need to be passed within the `fields` object. For example, to render a `tooltip` pattern, one would
use the following:

```json
{
  "label": "test",
  "settings": {
    "icon": true,
    "icontheme": "light",
    "theme": "dark"
  }
}
```

Note that any HTML needs to be set as Drupal `#markup`. For example, to render a `richtext` pattern, one would pass the
following JSON to the `fields` parameter:

```json
{
  "content": {
    "#markup": "<b>this is bold</b>, this is not"
  }
}
```

The test module above is enabled by default in both `dev` and `dist` Docker images, but it is not included in the released package.


[1]: https://github.com/openeuropa/task-runner
[2]: https://docs.docker.com/compose
