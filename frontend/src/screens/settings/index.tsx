import { RouteProp, useRoute } from '@react-navigation/native';
import { ScreenContent } from '~/components/ScreenContent';
import { StyleSheet, View } from 'react-native';

import { RootTabParamList } from '~/navigation';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { useDispatch } from 'react-redux';
import { logout } from '~/store/reducers/authReducer';

// type ProfileScreenRouteProp = RouteProp<RootTabParamList, 'Profile'>;

export default function Profile() {
    const dispatch = useDispatch();
    const handleLogout = () => {
        dispatch(logout());
    };

    return (
        <View style={styles.container}>
            <Button onPress={handleLogout}>
              <Text>Wyloguj się</Text>
            </Button>
        </View>
    );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 6,
  },
});
