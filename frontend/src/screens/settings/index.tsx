import { View } from 'react-native';

import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { useDispatch } from 'react-redux';
import { logout } from '~/store/reducers/authReducer';
import { LogOut } from '~/lib/icons/LogOut'
import { Switch } from '~/components/ui/switch';
import { Label } from '~/components/ui/label';
import { useColorScheme } from '~/lib/useColorScheme';
import { Separator } from '~/components/Separator';



export default function Profile() {
    const dispatch = useDispatch();
    const handleLogout = () => {
        dispatch(logout());
    };
    
    const { theme, toggleColorScheme } = useColorScheme();

    return (
        <View className="flex-1 p-6">
          <View className='flex-row items-center gap-2'>
            <Switch checked={theme.dark} onCheckedChange={toggleColorScheme} nativeID='dark-mode' />
            <Label
              nativeID='dark-mode'
              onPress={toggleColorScheme}
            >
              Ciemny motyw
            </Label>
          </View>
            
          <Separator />
          <Button variant={"link"} onPress={handleLogout} className="flex flex-row items-center gap-2">
            <LogOut className="dark:text-white text-black" width={24} height={24} />
            <Text>Wyloguj się</Text>
          </Button>
        </View>
    );
}