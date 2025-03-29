import { Avatar, AvatarImage, AvatarFallback }  from '~/components/ui/avatar';
import User from '~/lib/icons/User';
import { Text } from '~/components/ui/text';

type UserAvatarProps = {
    userEmail?: string;
    userName?: string;
    imageUrl?: string;
}

const UserAvatar = ({ userEmail, userName, imageUrl }: UserAvatarProps) => {

    return (
        <Avatar
            className="h-10 w-10 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700"
            alt={`Awatar użytkownika ${userName || userEmail}`}
        >
            {imageUrl ? (
                <>
                    <AvatarImage
                        source={{ uri: imageUrl }}
                    />
                    <AvatarFallback>
                        <Text>{`Awatar użytkownika ${userName || userEmail}`}</Text>
                    </AvatarFallback>
                </>
            ) : (
                <User className={"dark:text-white text-black"} />
            )}
        </Avatar>
    );
}

export default UserAvatar;