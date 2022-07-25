const path = require('path')
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
  entry: './src/game.ts',
  output: {
    path: process.env.NODE_ENV === "production" ? path.resolve(__dirname, 'dist') : path.resolve(__dirname),
    filename: 'bundle.js',
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        { from: "assets", to: "assets" },
        { from: "index.html", to: "index.html" },
      ],
    }),
  ],
  module: {
    rules: [
      {
        test: /\.ts$/,
        include: path.resolve(__dirname, 'src'),
        loader: 'ts-loader',
      },
      // {
      //   test: require.resolve('Phaser'),
      //   loader: 'expose-loader',
      //   options: { exposes: { globalName: 'Phaser', override: true } }
      // }
    ],
  },
  devServer: {
    static: path.resolve(__dirname, './'),
    // publicPath: '/dist/',
    host: 'localhost',
    port: 8080,
    open: false,
  },
  resolve: {
    extensions: ['.ts', '.js'],
  },
}
