This folder must contain only Sass abstract assets, for example: variables, mixins, functions, placeholders.

An "abstract asset" is a Sass file that **does not produce any CSS output**.

The file `index.scss` (and therefore every other imported asset listed there) is injected as an **@import** statement in every other Sass file involved in the Webpack building process. So, to avoid CSS rules duplications, only "abstract assets" are allowed here.

Ref: [https://github.com/webpack-contrib/sass-loader/issues/218](https://github.com/webpack-contrib/sass-loader/issues/218)
