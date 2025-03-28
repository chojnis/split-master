import { useColorScheme as useNativewindColorScheme } from 'nativewind';
import { NAV_THEME } from '~/lib/constants';
import { Theme, DefaultTheme, DarkTheme } from '@react-navigation/native';
import { useEffect } from 'react';

export function useColorScheme() {
  const { colorScheme, setColorScheme, toggleColorScheme } = useNativewindColorScheme();

  const LIGHT_THEME: Theme = {
    ...DefaultTheme,
    colors: {
      ...DefaultTheme.colors,
      ...NAV_THEME.light
    },
  };
  const DARK_THEME: Theme = {
    ...DarkTheme,
    colors: {
      ...DarkTheme.colors,
      ...NAV_THEME.dark
    }
  };

  return {
    colorScheme: colorScheme ?? 'dark',
    theme: colorScheme === 'dark' ? DARK_THEME : LIGHT_THEME,
    setColorScheme,
    toggleColorScheme,
  };
}