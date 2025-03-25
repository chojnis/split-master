import { StyleSheet, View, Text, FlatList, RefreshControl, Pressable } from 'react-native';
import { useGetGroupsQuery } from '~/api';
import { Group } from '~/api/entity';
import { useState } from 'react';
import { useLayoutEffect } from 'react';
import { Button } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation';
import { StackNavigationProp } from '@react-navigation/stack';

type GroupsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupsList'>;

export default function Groups() {
  const { data, isLoading, error, refetch } = useGetGroupsQuery();
  const [refreshing, setRefreshing] = useState(false);
  const navigation = useNavigation<GroupsStackNavigationProp>();

  useLayoutEffect(() => {
    navigation.setOptions({
      headerRight: () => (
        <Button 
          title="Stwórz" 
          onPress={() => navigation.navigate('AddGroup')} 
        />
      ),
    });
  }, [navigation]);

  const onRefresh = async () => {
    setRefreshing(true);
    await refetch();
    setRefreshing(false);
  };

  if (isLoading) return <Text>Loading...</Text>;
  if (error) return <Text>Error </Text>;

  const renderItem = ({ item }: { item: Group }) => (
    <View>
      <Pressable
        onPress={() => navigation.navigate('GroupDetails', { groupId: item.id })}
      >
        <Text>{item.groupName}</Text>
        <Text>{item.description}</Text>
      </Pressable>
    </View>
  );

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        renderItem={renderItem}
        keyExtractor={(item) => item.id}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 6,
  }
});
