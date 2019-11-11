pimcore.registerNS("pimcore.plugin.FactoryInstallBundle");

pimcore.plugin.FactoryInstallBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.FactoryInstallBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("FactoryInstallBundle ready!");
    }
});

var FactoryInstallBundlePlugin = new pimcore.plugin.FactoryInstallBundle();
