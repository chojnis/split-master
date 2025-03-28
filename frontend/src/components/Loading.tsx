import { useEffect } from 'react';
import Animated, { Easing, useSharedValue, useAnimatedStyle, withRepeat, withTiming } from 'react-native-reanimated';
import Loader from '~/lib/icons/Loader';

const Loading = () => {
    const rotation = useSharedValue(0);

    useEffect(() => {
        rotation.value = withRepeat(
            withTiming(360, { duration: 2000, easing: Easing.linear }),
            -1,
            false
        );
    }, [rotation]);

    const animatedStyle = useAnimatedStyle(() => ({
        transform: [{ rotate: `${rotation.value}deg` }],
    }));

    return (
        <Animated.View style={[animatedStyle, { alignItems: 'center', justifyContent: 'center', flex: 1 }]}>
            <Loader className="dark:text-black text-white" />
        </Animated.View>
    );
};

export default Loading;