const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath('public/build/website/')
  .setPublicPath('/build/website')
  .addEntry('app', './assets/website/app.js')
  .copyFiles({
    from: './assets/website/img',
    to: 'img/[path][name].[hash:8].[ext]',
    // pattern: /\.(png|svg|jpg|jpeg|ico|mp4)$/,
  })
  .copyFiles({
    from: './assets/website/files',
    to: 'files/[path][name].[hash:8].[ext]',
    // pattern: /\.(png|svg|jpg|jpeg|ico|mp4)$/,
  })
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
  })
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
  })
  .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
