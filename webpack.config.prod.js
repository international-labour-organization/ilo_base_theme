const commonConfig = require("./webpack.config.common.js");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

const config = {
  ...commonConfig,
  mode: "production",

  // This optimization block is called only in PRODUCTION mode:
  optimization: {
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
  },
};

module.exports = config;
