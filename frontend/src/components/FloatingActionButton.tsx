import { TypedStartListening } from '@reduxjs/toolkit';
import { StringToBoolean } from 'class-variance-authority/dist/types';
import React from 'react';
import { StyleSheet, ViewStyle, View } from 'react-native';
import Animated, { 
  useSharedValue, 
  useAnimatedStyle,
  withSpring,
  Easing 
} from 'react-native-reanimated';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import Plus from '~/lib/icons/Plus';

type FloatingActionButtonProps = {
  onPress: () => void;
  secondOnPress?: () => void;
  icon?: React.ReactNode;
  secondIcon?: React.ReactNode;
  secondClassName?: string;
  className?: string;
}

const FloatingActionButton = ({ onPress, secondOnPress, icon, secondIcon, className, secondClassName }: FloatingActionButtonProps) => {
  const scale = useSharedValue(1);
  const secondScale = useSharedValue(1);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  const secondAnimatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: secondScale.value }],
  }));

  const handlePressIn = (second?: boolean) => {
    // scale.value = withSpring(0.9);
    if(second) {
      secondScale.value = withSpring(0.9);
    } else {
      scale.value = withSpring(0.9);
    }
  };

  const handlePressOut = (second?: boolean) => {
    // scale.value = withSpring(1);
    if(second) {
      secondScale.value = withSpring(1);
    } else {
      scale.value = withSpring(1);
    }
  };

  return (
    <View className="absolute bottom-4 right-4 flex flex-column items-center justify-center gap-4">
      {secondOnPress && (
        <Animated.View 
          style={[styles.container, secondAnimatedStyle]}
        >
          <Button
            onPress={secondOnPress}
            onPressIn={() => handlePressIn(true)}
            onPressOut={() => handlePressOut(true)}
            className={`${secondClassName || ''}`}
          >
            {secondIcon || (
              <Plus className="dark:text-black text-white" width={24} height={24} />
            )}
          </Button>
        </Animated.View>
      )}
      <Animated.View 
        style={[styles.container, animatedStyle]}
      >
        <Button
          onPress={onPress}
          onPressIn={() => handlePressIn()}
          onPressOut={() => handlePressOut()}
          className={`${className || ''}`}
        >
          {icon || (
            <Plus className="dark:text-black text-white" width={24} height={24} />
          )}
        </Button>
      </Animated.View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    // position: 'absolute',
    // right: 16,
    // bottom: 16,
    borderRadius: 28,
    elevation: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
  },
});

export default FloatingActionButton;