import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '~/components/ui/card';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import Loading from '~/components/Loading';
import { useAcceptInviteMutation, useRejectInviteMutation } from '~/api';
import { Invite } from '~/api/types/entity';
import { useFocusEffect } from '@react-navigation/native';
import { useCallback } from 'react';

type InviteItemProps = {
    invite: Invite;
    refresh: () => void;
}
  
const InviteItem = ({ invite, refresh }: InviteItemProps) => {

    const [acceptInvite, {isLoading: isLoadingAccept, isSuccess: isSuccessAccept}] = useAcceptInviteMutation();
    const [rejectInvite, {isLoading: isLoadingReject, isSuccess: isSuccessReject}] = useRejectInviteMutation();

    useFocusEffect(
        useCallback(() => {
            if(isSuccessAccept || isSuccessReject) {
                refresh();
            }
        }, [isSuccessAccept, isSuccessReject])
    );

    return (
        <Card className={`dark:bg-[#101828] bg-gray-100 flex p-4 border-orange-500`}>
        <CardHeader>
          <CardDescription className="mb-2">Otrzymałeś zaproszenie do grupy</CardDescription>
          <CardTitle>{invite.group.groupName}</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-row justify-between">
          <Button 
            onPress={() => acceptInvite(invite.id)} 
            disabled={isLoadingAccept}
            className="bg-green-500"
          >
            {isLoadingAccept ?  <Loading /> : <Text>Akceptuj</Text>}
          </Button>
          <Button 
            onPress={() => rejectInvite(invite.id)} 
            disabled={isLoadingReject}
            className="bg-red-500"
          >
            {isLoadingReject ? <Loading /> : <Text>Odrzuć</Text>}
          </Button>
        </CardContent>
      </Card>
    );
}

export default InviteItem;