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
      </NavigationContainer>
    </ThemeProvider>
  );
}

export default Navigation;

const useIsomorphicLayoutEffect =
    Platform.OS === 'web' && typeof window === 'undefined' ? useEffect : useLayoutEffect;
