import React from 'react';
import { StyleSheet, ViewStyle } from 'react-native';
import Animated, { 
  useSharedValue, 
  useAnimatedStyle,
  withSpring,
  Easing 
} from 'react-native-reanimated';
import { Button } from '~/components/ui/button';
import Plus from '~/lib/icons/Plus';

type FloatingActionButtonProps = {
  onPress: () => void;
  className?: string;
}

const FloatingActionButton = ({ onPress, className }: FloatingActionButtonProps) => {
  const scale = useSharedValue(1);

  const animatedStyle = useAnimatedStyle(() => ({
    transform: [{ scale: scale.value }],
  }));

  const handlePressIn = () => {
    scale.value = withSpring(0.9);
  };

  const handlePressOut = () => {
    scale.value = withSpring(1);
  };

  return (
    <Animated.View 
      style={[styles.container, animatedStyle]}
    >
      <Button
        onPress={onPress}
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
      >
        <Plus className="dark:text-black text-white" width={24} height={24} />
      </Button>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    right: 16,
    bottom: 16,
    borderRadius: 28,
    elevation: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
  },
});

export default FloatingActionButton;