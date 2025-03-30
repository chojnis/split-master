import { View } from 'react-native';
import Loader from '~/lib/icons/Loader';

type LoadingProps = {
    reverseColors?: boolean;
    absolute?: boolean;
    className?: string;
}

const Loading = ({ reverseColors, absolute, className }: LoadingProps) => {
    const darkColor = reverseColors ? 'dark:text-white' : 'dark:text-black';
    const lightColor = reverseColors ? 'text-black' : 'text-white';

    return (
        <View 
            className={`flex-1 items-center justify-center ${absolute ? "absolute w-full h-full opacity-70 z-10 dark:bg-black bg-white" : ""} ${className || ''}`}
        >
            <View className="animate-spin">
                <Loader 
                    className={`${darkColor} ${lightColor}`} 
                />
            </View>
        </View>
    );
};

export default Loading;