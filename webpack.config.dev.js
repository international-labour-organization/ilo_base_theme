const commonConfig = require("./webpack.config.common.js");

const config = {
  ...commonConfig,
  mode: "development",
  watch: false,
  devtool: "eval-source-map",

  watchOptions: {
    poll: 500,
    ignored: ["node_modules/**", "assets/**"],
  },
};

module.exports = config;
