import { NavigationContainer, ThemeProvider } from '@react-navigation/native';
import { StatusBar } from 'expo-status-bar';
import { useState, useRef, useEffect, useLayoutEffect } from 'react';
import { Platform } from 'react-native';
import { useColorScheme } from '~/lib/useColorScheme';
import { useSelector } from 'react-redux';
import { RootState } from '~/store';
import AuthStack from './auth';
import RootTab from './root';
import { PortalHost } from '@rn-primitives/portal';
import FlashMessage from "react-native-flash-message";

const Navigation = () => {
  const isAuthenticated = useSelector((state: RootState) => state.auth.isAuthenticated);
  const hasMounted = useRef(false);
  const { colorScheme, theme } = useColorScheme();
  const [isColorSchemeLoaded, setIsColorSchemeLoaded] = useState(false);

  useIsomorphicLayoutEffect(() => {
    if (hasMounted.current) {
      return;
    }

    setIsColorSchemeLoaded(true);
    hasMounted.current = true;
  }, []);

  if (!isColorSchemeLoaded) {
    return null;
  }

  return (
    <ThemeProvider value={theme}>
      <NavigationContainer theme={theme}>
        <StatusBar style={colorScheme === 'dark' ? 'light' : 'dark'} />
        {!isAuthenticated ? <AuthStack /> : <RootTab />}
        <PortalHost />
        <FlashMessage
          position="bottom"
          floating={true}
          animated={true}
          icon="auto"
          duration={1000}
          hideOnPress={true}
          style={{
            marginBottom: 50,
            zIndex: 1000,
            borderRadius: 10,
            padding: 10,
            width: '90%',
            alignSelf: 'center',
          }}
        />
      </NavigationContainer>
    </ThemeProvider>
  );
}

export default Navigation;

const useIsomorphicLayoutEffect =
    Platform.OS === 'web' && typeof window === 'undefined' ? useEffect : useLayoutEffect;
