import { View, FlatList, RefreshControl, Pressable } from 'react-native';
import { useGetGroupsQuery } from '~/api';
import { Group } from '~/api/types/entity';
import { useState } from 'react';
import { useNavigation } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import GroupItem from '~/components/group/GroupItem';
import FloatingActionButton from '~/components/FloatingActionButton';
import { Container } from '~/components/Container';
import { useFocusEffect } from '@react-navigation/native';
import { useCallback } from 'react';
import Loading from '~/components/Loading';
import ErrorText from '~/components/ErrorText';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';

type GroupsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupsList'>;

export default function Groups() {
  const { data, isLoading, error, refetch } = useGetGroupsQuery();
  const [refreshing, setRefreshing] = useState(false);
  const navigation = useNavigation<GroupsStackNavigationProp>();

  useFocusEffect(
    useCallback(() => {
      refetch();
    }, [refetch])
  );

  const onRefresh = async () => {
    setRefreshing(true);
    await refetch();
    setRefreshing(false);
  };

  if (isLoading) return <Loading reverseColors />;
  if (error) {
    return (
      <Container>
        <ErrorText className="mb-4">Wystąpił błąd podczas ładowania grup</ErrorText>
          <Button
            variant="link"
            onPress={onRefresh}
          >
            <Text>Spróbuj ponownie</Text>
          </Button>
      </Container>
    )
  }
  const renderItem = ({ item, index }: { item: Group, index: number }) => (
    <View
      className={`${index > 0 ? 'mt-4' : ''}`}
    >
      <Pressable
        onPress={() => navigation.navigate('GroupDetails', { groupId: item.id })}
      >
        <GroupItem groupName={item.groupName} description={item.description} />
      </Pressable>
    </View>
  );

  return (
    // <Container>
    <>
      <FlatList
        data={data}
        renderItem={renderItem}
        keyExtractor={(item) => item.id}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        className={'flex flex-1 p-6'}
      />

      <FloatingActionButton onPress={() => navigation.navigate('AddGroup')} />
    </>
    // </Container>
  );
}
