import { View, FlatList, RefreshControl } from 'react-native';
import { useState } from 'react';
import { useLayoutEffect } from 'react';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { useGetUserQuery } from '~/api';
import Loading from '~/components/Loading';
import { Container } from '~/components/Container';
import ErrorText from '~/components/ErrorText';

// type UserDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'UserDetails'>;
type UserDetailsScreenRouteProp = RouteProp<GroupsStackParamList, 'UserDetails'>;

export default function UserDetails() {
    const router = useRoute<UserDetailsScreenRouteProp>();
    const userId = router.params.userId;

    const { data, isLoading, error, refetch } = useGetUserQuery(userId);
    const [refreshing, setRefreshing] = useState(false);
    // const navigation = useNavigation<UserDetailsStackNavigationProp>();

    const onRefresh = async () => {
        setRefreshing(true);
        await refetch();
        setRefreshing(false);
    };

    if (isLoading) return <Loading />
    if (error) {
        return (
            <Container>
                <ErrorText>Wystąpił błąd podczas wczytywania użytkownika.</ErrorText>
                <Button onPress={onRefresh}>
                    <Text>Spróbuj ponownie</Text>
                </Button>
            </Container>
        )
    };

    // return (

    // );
}