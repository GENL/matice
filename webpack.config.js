const path = require( 'path' );

module.exports = {
  // bundling mode
  mode: 'production',
  target: "web",
  // entry files
  entry: './src/js/matice.ts',
  devtool: 'cheap-source-map',
  // output bundles (location)
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'matice.min.js',
  },
  // file resolutions
  resolve: {
    extensions: [ '.ts', '.js' ],
  },
  // loaders
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: 'ts-loader',
        exclude: /node_modules/,
      },
    ]
  },
};
