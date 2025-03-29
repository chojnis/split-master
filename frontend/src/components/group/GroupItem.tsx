import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '~/components/ui/card';
import { Text } from '~/components/ui/text';

type GroupItemProps = {
    groupName: string;
    description: string;
}
  
const GroupItem = ({ groupName, description }: GroupItemProps) => {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{groupName}</CardTitle>
                <CardDescription>{description}</CardDescription>
            </CardHeader>
            <CardContent>
                <Text>Card Content</Text>
            </CardContent>
            <CardFooter>
                <Text>Card Footer</Text>
            </CardFooter>
        </Card>
    );
}

export default GroupItem;