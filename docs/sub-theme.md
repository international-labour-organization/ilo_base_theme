# How to create a sub-theme

To create a sub-theme of the `ilo_base_theme` based on the provided `ilo_base_theme.info.yml` file, you need to:

1. **Create a new directory for the sub-theme**: Place it under the `themes/custom/` directory (e.g., `themes/custom/my_sub_theme`).
2. **Create the `.info.yml` file**: This file defines the sub-theme's properties and its parent.

Here’s what the sub-theme `.info.yml` file would look like:

```yaml
name: My Sub Theme
type: theme
description: A sub-theme of the ILO base theme.
base theme: ilo_base_theme
core_version_requirement: '^9 || ^10 || ^11'

regions:
  breadcrumb: Breadcrumb
  content: Content
  header: Header
  footer: Footer

dependencies:
  - ilo_base_theme:ilo_base_theme_companion
```

The sub-theme will automatically inherit the templates from the base theme. If you need to override templates, copy them from the `ilo_base_theme` directory into the sub-theme and modify as needed.

You can now enable your sub-theme through the Drupal admin UI or using Drush:

```bash
drush theme:enable my_sub_theme
drush config:set system.theme default my_sub_theme
drush cr
```
