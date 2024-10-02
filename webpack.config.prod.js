import commonConfig from "./webpack.config.common.js";
import CssMinimizerPlugin from "css-minimizer-webpack-plugin";
import TerserPlugin from "terser-webpack-plugin";

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

export default config;
