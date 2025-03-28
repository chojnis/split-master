const { getDefaultConfig } = require('expo/metro-config');
const { withNativeWind } = require('nativewind/metro');
const { wrapWithReanimatedMetroConfig } = require('react-native-reanimated/metro-config');

// eslint-disable-next-line no-undef
const config = getDefaultConfig(__dirname);

// module.exports = withNativeWind(config, { input: './global.css' });
const nativeWindConfig = withNativeWind(config, { input: './global.css' });

module.exports = wrapWithReanimatedMetroConfig(nativeWindConfig);

