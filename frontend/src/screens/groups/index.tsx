import { View, FlatList, RefreshControl, Pressable } from 'react-native';
import { useLazyGetGroupsQuery, useGetInvitesQuery } from '~/api';
import { Group, Invite } from '~/api/types/entity';
import { useEffect, useState } from 'react';
import { useNavigation } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import GroupItem from '~/components/group/GroupItem';
import FloatingActionButton from '~/components/FloatingActionButton';
import { useFocusEffect } from '@react-navigation/native';
import { useCallback } from 'react';
import Loading from '~/components/Loading';
import Error from '~/components/Error';
import HousePlus from '~/lib/icons/HousePlus'; 
import { Text } from '~/components/ui/text';
import InviteItem from '~/components/group/InviteItem';

type GroupsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupsList'>;

/**
 * Screen component for displaying user's groups and invites.
 * 
 * This component manages the state for displaying groups that a user belongs to and pending invites.
 * It implements pagination for groups, with infinite scrolling capabilities, and provides functionality
 * to refresh the data and navigate to group details or add new groups.
 * 
 */
export default function Groups() {
  const [groups, setGroups ] = useState<Group[]>([]);
  const [page, setPage ] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [trigger, { isLoading, isFetching, isError, isSuccess }] = useLazyGetGroupsQuery();

  const { data: invites, refetch: refetchInvites } = useGetInvitesQuery();

  const [refreshing, setRefreshing] = useState(false);
  const navigation = useNavigation<GroupsStackNavigationProp>();

  const loadInitPage = async () => {
    const { data, isSuccess } = await trigger(1);
    setGroups(isSuccess ? data : []);
    setPage(isSuccess ? 2 : 1);
    setHasMore(isSuccess ? data.length !== 0 : true);
  }

  const loadNextPage = async (manual?: boolean) => {
    if(!hasMore || isFetching || (!manual && isError)) return;
    if(page === 1) return loadInitPage();
    const { data, isSuccess } = await trigger(page);
    if(isSuccess) {
      setGroups((prev) => [...prev, ...data]);
      setPage((prev) => prev + 1);
      if(data.length === 0) {
        setHasMore(false);
      }
    }
  }

  const onRefresh = async () => {
    setRefreshing(true);
    await loadInitPage();
    await refetchInvites();
    setRefreshing(false);
  };

  useEffect(() => {
    loadInitPage();
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadInitPage();
      refetchInvites();
    }, [])
  );

  const renderInviteItem = ({ item, index }: { item: Invite, index: number }) => (
    <View className={`${index > 0 ? 'mt-4' : 'mt-6'}`}>
      <InviteItem invite={item} refresh={onRefresh} />
    </View>
  );

  const renderItem = ({ item, index }: { item: Group, index: number }) => (
    <View className={`${index > 0 ? 'mt-4' : 'mt-6'}`}>
      <Pressable
        onPress={() => navigation.navigate('GroupDetails', { groupId: item.id })}
      >
        <GroupItem groupName={item.groupName} description={item.description} />
      </Pressable>
    </View>
  );

  const showLoading = isLoading || refreshing || (groups.length === 0 && isFetching);

  return (
    <>
      {showLoading && <Loading absolute reverseColors />}

        <View className="flex">

          <FlatList
            data={invites}
            renderItem={renderInviteItem}
            keyExtractor={(item) => item.id}
            refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
            className="flex px-6"
          />

          <FlatList
            data={groups}
            renderItem={renderItem}
            keyExtractor={(item) => item.id}
            onEndReached={()=>loadNextPage()}
            onEndReachedThreshold={0.5}
            refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
            className={'flex px-6'}
            ListEmptyComponent={() => (
                isSuccess && (
                  <View className="flex items-center justify-center mt-6">
                    <Text className="dark:text-gray-300 text-gray-500">Nie jesteś w żadnej grupie</Text>
                  </View>
                )
            )}
            ListFooterComponent={() => (
              <>
                {isError && !isFetching && (
                  <Error className="mt-4" onRefresh={()=>loadNextPage(true)} message="Wystąpił błąd podczas ładowania grup" />
                )}
                {/* {isFetching && !showLoading && groups.length > 0 && (
                  <Loading className="mt-4" reverseColors />
                )} */}
                <View className="h-6"></View>
              </>
            )}
          />
        </View>

      <FloatingActionButton 
        onPress={() => navigation.navigate('AddGroup')} 
        icon={<HousePlus className="dark:text-black text-white" width={24} height={24} />}
      />
    </>
  );
}
