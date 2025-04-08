import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '~/components/ui/card';
import House from '~/lib/icons/House';
import { View } from 'react-native';

type GroupItemProps = {
    groupName: string;
    description: string;
}
  
const GroupItem = ({ groupName, description }: GroupItemProps) => {
    return (
        <Card className="dark:bg-[#101828] bg-gray-100 w-full border-transparent">
            <CardHeader className="flex-row items-center gap-6">
                <House className="dark:text-white text-black" width={32} height={32} />
                <View>
                    <CardTitle>{groupName}</CardTitle>
                    {description && description.length > 0 && (
                        <CardDescription>{description}</CardDescription>
                    )}
                </View>
            </CardHeader>
        </Card>
    );
}

export default GroupItem;