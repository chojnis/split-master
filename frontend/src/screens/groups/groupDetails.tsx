import { View } from 'react-native';
import { useGetGroupQuery } from '~/api';
import { useCallback, useState } from 'react';
import { useLayoutEffect } from 'react';
import { Button } from '~/components/ui/button';	
import { Text } from '~/components/ui/text';
import { useNavigation, useRoute, RouteProp, useFocusEffect } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { Container } from '~/components/Container';
import TransactionsSection from '~/components/group/TransactionSection';
import Loading from '~/components/Loading';
import ErrorText from '~/components/ErrorText';
import FloatingActionButton from '~/components/FloatingActionButton';
import { ListPlus } from '~/lib/icons/ListPlus';
import Settings from '~/lib/icons/Settings';

type GroupDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupDetails'>;
type GroupDetailsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupDetails'>;

export default function GroupDetails() {
    const router = useRoute<GroupDetailsScreenRouteProp>();
    const groupId = router.params.groupId;

    const { data, isLoading, isFetching, isError, refetch } = useGetGroupQuery(groupId);

    const [refreshing, setRefreshing] = useState(false);
    const navigation = useNavigation<GroupDetailsStackNavigationProp>();

    const onRefresh = async () => {
      setRefreshing(true);
      await refetch();
      setRefreshing(false);
    };

    useFocusEffect(
        useCallback(() => {
            refetch();
        }, [])
    );

    useLayoutEffect(() => {
      navigation.setOptions({
        headerRight: () => (
          <Button 
            onPress={() => navigation.navigate("GroupSettings", { groupId })}
            variant={null}
          >
            <Settings className="dark:text-white text-black" width={24} height={24} />
          </Button>
        ),
      });
    }, [navigation, groupId]);

    if (isLoading) return <Loading absolute reverseColors />;
    if (isError || !data) {
      return (
        <Container>
          <ErrorText className="mb-4">Wystąpił błąd podczas ładowania grupy</ErrorText>
            <Button
              variant="link"
              onPress={onRefresh}
            >
              <Text>Spróbuj ponownie</Text>
            </Button>
        </Container>
      )
    }

    return (
        <>
          {isFetching && <Loading absolute reverseColors />}
          <Container>
              <View>
                <Text className={"text-4xl"}>{data.groupName}</Text>
                <Text>{data.description}</Text>
              </View>
              <TransactionsSection groupId={groupId} defaultCurrencyId={data.currency.id} navigation={navigation} />
          </Container>
          <FloatingActionButton 
            onPress={() => navigation.navigate('AddTransaction', { groupId: data.id, defaultCurrencyId: data.currency.id })} 
            icon={<ListPlus className="text-white" width={24} height={24} />}
            className={"bg-green-500"}
          />
        </>
    );
}