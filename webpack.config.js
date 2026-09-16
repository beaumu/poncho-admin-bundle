const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('./public/')
    .setPublicPath('.')
    .setManifestKeyPrefix('')

    .addEntry('poncho_admin', './assets/admin.js')

    .enableSassLoader((options) => {
        options.sassOptions = {
            quietDeps: true,
            // Bootstrap 5 is still @import-based (119 @import, 0 @use in 5.3.8),
            // so this bundle cannot move to the Sass module system yet. Silence
            // the notice rather than pretend; revisit when Bootstrap ships @use.
            silenceDeprecations: ['import'],
        }
    })

    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .autoProvidejQuery()

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
;

module.exports = Encore.getWebpackConfig();
