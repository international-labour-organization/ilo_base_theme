import path from 'path';
import { glob } from 'glob';
import MiniCssExtractPlugin from "mini-css-extract-plugin";
import RemoveEmptyScriptsPlugin from 'webpack-remove-empty-scripts';
import PostCSSAssetsPlugin from 'postcss-assets-webpack-plugin';
import sortMediaQueries from 'postcss-sort-media-queries';
import CssMinimizerPlugin from "css-minimizer-webpack-plugin";
import TerserPlugin from "terser-webpack-plugin";

const rootDir = path.resolve(import.meta.dirname);
const buildDir = 'dist';

// Define all entries:
const globals = ['./css/global.scss'];
const components = glob.sync('./templates/**/*.{scss,js}', {
  dotRelative: true,
  ignore: [
    "./templates/**/_*",    // Exclude from the bundle process files starting with '_'.
    "./templates/**/_*/**"  // Exclude from the bundle process entire directories starting with '_'.
  ]
});

const allMatches = [...globals, ...components];

const localOutputPath = (filePath) => {
  const fileDir = path.dirname(filePath);
  const fileBasename = path.basename(filePath);
  const fileExtension = path.extname(fileBasename);
  const fileName = path.basename(fileBasename, fileExtension);
  let fileExtensionDir = 'ERROR/';

  switch (fileExtension) {
    case '.js':
      fileExtensionDir = 'js/';
      break;

    case '.scss':
      fileExtensionDir = 'css/';
      break;
  }

  return fileName + '/' + fileExtensionDir + fileName + '.processed';
};

const entry = allMatches.reduce((acc, match) => {
  acc[localOutputPath(match)] = match;
  return acc;
}, {});

export default (env, argv) => {
  const isProduction = argv.mode === 'production';

  const config = {
    entry: entry,
    output: {
      path: rootDir + "/" + buildDir,
      filename: "[name].js",
      clean: true,
    },
    module: {
      rules: [
        {
          test: /\.scss$/,
          exclude: [/node_modules/],
          use: [
            {
              loader: MiniCssExtractPlugin.loader
            },
            {
              loader: "css-loader",
              options: {
                sourceMap: !isProduction
              }
            },
            {
              loader: 'postcss-loader',
              options: {
                sourceMap: !isProduction,
                postcssOptions: {
                  plugins: [
                    [
                      "autoprefixer",
                      {},
                    ],
                  ],
                },
              },
            },
            {
              loader: "sass-loader",
              options: {
                sourceMap: !isProduction,
                additionalData: `@import "${rootDir}/abstractions/index";`
              }
            }
          ]
        },
        {
          test: /\.(js|jsx)$/,
          exclude: /node_modules/,
          use: { loader: 'babel-loader' }
        },
        {
          test: /\.(png|jp(e*)g|svg|gif)$/,
          exclude: /node_modules/,
          type: 'asset',
          generator: {
            filename: (pathData) => {
              const pathParts = pathData.filename.split("/");
              const imagesIndex = pathParts.findIndex(k => k === 'images');
              return pathParts.slice(imagesIndex - 1).join('/');
            }
          }
        }
      ]
    },
    resolve: {
      extensions: [".js"],
      alias: {
        Components: path.resolve(import.meta.dirname, "templates/"),
      },
    },
    plugins: [
      new RemoveEmptyScriptsPlugin(),
      new MiniCssExtractPlugin({
        filename: '[name].css'
      }),
      new PostCSSAssetsPlugin({
        test: /\.css$/,
        log: false,
        plugins: [
          sortMediaQueries
        ]
      }),
    ],
    stats: {
      preset: 'minimal',
    },
    mode: argv.mode,
    devtool: isProduction ? false : 'eval-source-map',
    watch: !isProduction,
    watchOptions: {
      poll: !isProduction ? 500 : undefined,
      ignored: ["node_modules/**", "assets/**"],
    },
    optimization: isProduction ? {
      minimize: true,
      minimizer: [
        new TerserPlugin({
          parallel: true,
          terserOptions: {
            format: {
              comments: false,
            },
          },
          extractComments: false,
        }),
        new CssMinimizerPlugin({
          minimizerOptions: {
            preset: [
              "default",
              {
                discardComments: { removeAllButFirst: true },
              },
            ],
          },
        }),
      ],
    } : undefined,
  };

  return config;
};
