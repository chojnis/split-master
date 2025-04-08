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
import SettlementsSection from '~/components/group/SettlementsSection';
import Loading from '~/components/Loading';
import ErrorText from '~/components/ErrorText';
import FloatingActionButton from '~/components/FloatingActionButton';
import { ListPlus } from '~/lib/icons/ListPlus';
import Settings from '~/lib/icons/Settings';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '~/components/ui/tabs';

type GroupDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupDetails'>;
type GroupDetailsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupDetails'>;

export default function GroupDetails() {
    const router = useRoute<GroupDetailsScreenRouteProp>();
    const groupId = router.params.groupId;

    const { data, isLoading, isFetching, isError, refetch } = useGetGroupQuery(groupId);

    const [refreshing, setRefreshing] = useState(false);
    const navigation = useNavigation<GroupDetailsStackNavigationProp>();

    const [value, setValue] = useState('transactions');

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
          {/* {isFetching && <Loading absolute reverseColors />} */}
          <Container>
              <View className="flex flex-col gap-2 justify-center items-center">
                <Text className={"text-4xl"}>{data.groupName}</Text>
                {(data.description && data.description.length > 0) && <Text className="dark:text-gray-300 text-gray-500 text-2xl">{data.description}</Text>}
              </View>
              <Tabs
                  value={value}
                  onValueChange={setValue}
                  className='w-full max-w-[400px] mx-auto flex-col gap-1.5 mt-4'
                >
                <TabsList className='flex-row w-full bg-gray-100 dark:bg-[#101828]'>
                  <TabsTrigger value='transactions' className={`flex-1 ${value === 'transactions' ? 'dark:bg-[#1e2939] bg-white' : ''}`}>
                    <Text>Transakcje</Text>
                  </TabsTrigger>
                  <TabsTrigger value='settlements' className={`flex-1 ${value === 'settlements' ? 'dark:bg-[#1e2939] bg-white' : ''}`}>
                    <Text>Spłaty</Text>
                  </TabsTrigger>
                </TabsList>
                <TabsContent value='transactions'>
                  <TransactionsSection groupId={groupId} defaultCurrency={data.currency} navigation={navigation} />
                </TabsContent>
                <TabsContent value='settlements'>
                  <SettlementsSection groupId={groupId} navigation={navigation} />
                </TabsContent>
              </Tabs>
          </Container>
          <FloatingActionButton 
            onPress={() => navigation.navigate('AddTransaction', { groupId: data.id, defaultCurrency: data.currency })} 
            icon={<ListPlus className="text-white" width={24} height={24} />}
            className={"bg-green-500"}
          />
        </>
    );
}