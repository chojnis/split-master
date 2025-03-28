import { StyleSheet, View, Text, FlatList, RefreshControl, Pressable } from 'react-native';
import { useGetGroupsQuery } from '~/api';
import { Group } from '~/api/types/entity';
import { useState } from 'react';
import { useNavigation } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import GroupCard from '~/components/GroupCard';
import FloatingActionButton from '~/components/FloatingActionButton';
import { Container } from '~/components/Container';
import { useFocusEffect } from '@react-navigation/native';
import { useCallback } from 'react';

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

  if (isLoading) return <Text>Loading...</Text>;
  if (error) return <Text>Error </Text>;

  const renderItem = ({ item, index }: { item: Group, index: number }) => (
    <View
      className={`${index > 0 ? 'mt-4' : ''}`}
    >
      <Pressable
        onPress={() => navigation.navigate('GroupDetails', { groupId: item.id })}
      >
        <GroupCard groupName={item.groupName} description={item.description} />
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

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 6,
  }
});
