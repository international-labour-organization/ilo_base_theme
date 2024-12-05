# NPM Commands

This documentation outlines the purpose and functionality of the commands defined in the `package.json` file for this Node.js project.

## Commands

### **`design-system:dist`**
```bash
npm run design-system:dist
```
- **Description**: Compiles and copies assets from the ILO Design System into the project.
- **Details**:
  - Utilizes `webpack.design-system.js` for the build process.
  - Assets such as CSS, fonts, and brand assets are sourced from the `@ilo-org` package and placed in the `ilo_base_theme_companion` module under `dist/`.
  - This is essential to ensure the Design System components are available for sites that do not use the base theme.

---

### **`theme:dev`**
```bash
npm run theme:dev
```
- **Description**: Compiles SCSS and other assets specific to the local Drupal theme in development mode.
- **Details**:
  - Uses `webpack.theme.js` with `--mode=development`.
  - Provides source maps and enables a development-friendly setup for SCSS and JavaScript assets.

---

### **`theme:prod`**
```bash
npm run theme:prod
```
- **Description**: Compiles SCSS and other assets specific to the local Drupal theme in production mode.
- **Details**:
  - Uses `webpack.theme.js` with `--mode=production`.
  - Optimizes the assets by minimizing JavaScript and CSS.

---

### **`theme:build`**
```bash
npm run theme:build
```
- **Description**: Runs the `theme:prod` and `design-system:dist` commands in sequence.
- **Details**:
  - Ensures both local theme assets and Design System assets are built and available for deployment.
  - Combines efforts to support the local theme and make Design System components accessible for the `ilo_base_theme_companion` module.

---

### **`patch:generate`**
```bash
npm run patch:generate
```
- **Description**: Generates a patch for the `@ilo-org/twig` package.
- **Details**:
  - Utilizes the `patch-package` tool to create a patch file for custom modifications to `@ilo-org/twig`.

---

### **`postinstall`**
```bash
npm run postinstall
```
- **Description**: Automatically applies all patches after dependencies are installed.
- **Details**:
  - Runs `patch-package` to apply patches such as those for `@ilo-org/twig` during the `npm install` process.

---

## Webpack Configurations

### **`webpack.design-system.js`**
- **Purpose**: Copies assets from the ILO Design System into the `ilo_base_theme_companion` module.
- **Key Features**:
  - **CopyWebpackPlugin**: Used to transfer CSS, fonts, and other assets from the `@ilo-org` package to `modules/ilo_base_theme_companion/dist/`.
  - Ensures the module can be enabled independently of the base theme.

---

### **`webpack.theme.js`**
- **Purpose**: Compiles SCSS and JavaScript specific to the local Drupal theme.
- **Key Features**:
  - Supports both development and production modes.
  - **SCSS Compilation**: Handles global and component-level SCSS, excluding files starting with `_`.
  - **Optimization**: Minimizes CSS and JavaScript in production mode using `CssMinimizerPlugin` and `TerserPlugin`.
  - **PostCSS**: Enhances CSS with tools like `autoprefixer` and `postcss-sort-media-queries`.

---

## Key Concepts

### Local Theme Assets
- Managed by `theme:*` commands.
- Includes SCSS and JavaScript files specific to the Drupal theme.

### Design System Assets
- Managed by `design-system:*` commands.
- Copied from the ILO Design System project into the `ilo_base_theme_companion` module, ensuring reusability for sites not using the base theme.

### Integration
- The `theme:build` command ensures both local and design system assets are fully built and ready for deployment.
