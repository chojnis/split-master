import React from 'react';
import { useColorScheme } from '~/lib/useColorScheme';
import { MoonStar } from '~/lib/icons/MoonStar';
import { Sun } from '~/lib/icons/Sun';
import { Button } from '~/components/ui/button';
import Animated, {
    useSharedValue,
    useAnimatedStyle,
    withSpring,
    withTiming,
    Easing,
} from 'react-native-reanimated';

const ToggleTheme = () => {
    const { theme, toggleColorScheme } = useColorScheme();
    const scale = useSharedValue(1);
    const rotation = useSharedValue(0);

    const Icon = theme.dark ? Sun : MoonStar;
    const AnimatedIcon = Animated.createAnimatedComponent(Icon);

    const handlePress = () => {
        scale.value = withSpring(0.8, { damping: 2 });
        
        rotation.value = withTiming(rotation.value + 180, {
            duration: 300,
            easing: Easing.linear,
        });
        
        setTimeout(() => {
            scale.value = withSpring(1, { damping: 10 });
        }, 100);
        
        toggleColorScheme();
    }

    const animatedStyle = useAnimatedStyle(() => ({
        transform: [
            { scale: scale.value },
            { rotate: `${rotation.value}deg` },
        ]
    }));

    return (
        <Button 
            onPress={handlePress}
            variant={null}
        >
            <AnimatedIcon 
                width={24}
                height={24}
                fill={theme.colors.text}
                color={theme.colors.text}
                style={animatedStyle}
            />
        </Button>
    );

};

export default ToggleTheme;
