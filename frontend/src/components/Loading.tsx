import { View } from 'react-native';
import Loader from '~/lib/icons/Loader';

type LoadingProps = {
    reverseColors?: boolean;
}

const Loading = ({ reverseColors }: LoadingProps) => {
    const darkColor = reverseColors ? 'dark:text-white' : 'dark:text-black';
    const lightColor = reverseColors ? 'text-black' : 'text-white';

    return (
        <View 
            className={`flex-1 items-center justify-center animate-spin`}
        >
            <Loader 
                className={`${darkColor} ${lightColor}`} 
            />
        </View>
    );
};

export default Loading;