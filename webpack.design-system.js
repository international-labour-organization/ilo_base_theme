import path from 'path';
import CopyWebpackPlugin from 'copy-webpack-plugin';

const rootDir = path.resolve(import.meta.dirname);

const config = {
  mode: 'production',
  context: rootDir,
  entry: {}, // No entry points needed for this task
  output: {
    path: path.resolve(rootDir, 'modules/ilo_base_theme_companion/dist'),
    filename: '[name].js', // Placeholder, not used
    clean: true,
  },
  plugins: [
    new CopyWebpackPlugin({
      patterns: [
        { from: 'twig/dist/components', to: 'components', context: "node_modules/@ilo-org" },
        { from: 'styles/css/index.css', to: 'css/index.css', context: "node_modules/@ilo-org" },
        { from: 'styles/css/global.css', to: 'css/global.css', context: "node_modules/@ilo-org" },
        { from: 'fonts/assets', to: 'fonts/assets', context: "node_modules/@ilo-org" },
        { from: 'fonts/font-css', to: 'fonts/font-css', context: "node_modules/@ilo-org" },
        { from: 'brand-assets/dist/assets', to: 'assets', context: "node_modules/@ilo-org" },
      ],
    }),
  ],
  stats: 'minimal',
};

export default config;
