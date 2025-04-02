import { View, FlatList, RefreshControl, Pressable } from 'react-native';
import { useGetGroupsQuery, useGetInvitesQuery, useAcceptInviteMutation, useRejectInviteMutation } from '~/api';
import { Group, Invite } from '~/api/types/entity';
import { useState } from 'react';
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
import { Button } from '~/components/ui/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription
} from '~/components/ui/card';
import { Toast } from 'toastify-react-native';

type GroupsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupsList'>;

export default function Groups() {
  const { data, isLoading, isFetching, error, refetch } = useGetGroupsQuery();
  const { data: invites, isLoading: isLoadingInvites, refetch: refetchInvites } = useGetInvitesQuery();
  const [acceptInvite, {isLoading: isLoadingAccept, error: errorAccept}] = useAcceptInviteMutation();
  const [rejectInvite, {isLoading: isLoadingReject, error: errorReject}] = useRejectInviteMutation();

  const [refreshing, setRefreshing] = useState(false);
  const navigation = useNavigation<GroupsStackNavigationProp>();

  useFocusEffect(
    useCallback(() => {
      refetch();
      refetchInvites();
    }, [refetch, acceptInvite, rejectInvite])
  );

  const onRefresh = async () => {
    setRefreshing(true);
    await refetch();
    await refetchInvites();
    setRefreshing(false);
  };

  const showLoading = isLoading || refreshing;

  if(showLoading && error) return <Loading reverseColors />
  
  const renderInviteItem = ({ item }: { item: Invite }) => (
    <Card className="flex flex-1 mb-4 p-4 border-orange-500">
      <CardHeader>
        <CardDescription className="mb-2">Otrzymałeś zaproszenie do grupy</CardDescription>
        <CardTitle>{item.group.groupName}</CardTitle>
      </CardHeader>
      <CardContent className="flex flex-row justify-between">
        <Button 
          onPress={() => acceptInvite(item.id)} 
          disabled={isLoadingAccept}
          className="bg-green-500"
        >
          {isLoadingAccept ? (
            <Loading />
          ) : (
            <Text>Akceptuj</Text>
          )}
        </Button>
        <Button 
          onPress={() => rejectInvite(item.id)} 
          disabled={isLoadingReject}
          className="bg-red-500"
        >
          {isLoadingReject ? (
            <Loading />
          ) : (
            <Text>Odrzuć</Text>
          )}
        </Button>
      </CardContent>
    </Card>
  );

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
    <>
      {showLoading && <Loading absolute reverseColors />}

      {error ? (
        <Error onRefresh={onRefresh} message="Wystąpił błąd podczas ładowania grup" />
      )  : (
        <>

          {invites && invites.length > 0 && (
            <FlatList
              data={invites}
              renderItem={renderInviteItem}
              keyExtractor={(item) => item.id}
              refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
              className="flex flex-1 p-6"
            />
          )}

          <FlatList
            data={data}
            renderItem={renderItem}
            keyExtractor={(item) => item.id}
            refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
            className={'flex flex-1 p-6'}
          />
        </>
      )}

      <FloatingActionButton 
        onPress={() => navigation.navigate('AddGroup')} 
        icon={<HousePlus className="dark:text-black text-white" width={24} height={24} />}
      />
    </>
  );
}
