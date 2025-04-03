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
        </Card>
    );
}

export default GroupItem;