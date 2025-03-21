import { View, Text } from 'react-native';
import Form from '~/components/Form';
import { FormFieldType } from '~/components/Form';

function AddGroup () {

    const handleSubmit = (data: { [key: string]: string | number }) => {
        console.log(data);
    };

    const fields = [
        { label: 'Nazwa grupy', name: 'groupName', type: 'text' as FormFieldType },
        { label: 'Opis', name: 'description', type: 'text' as FormFieldType },
    ];
    
    return (
        <View>
            <Text>Dodaj grupę</Text>
            <Form fields={fields} onSubmit={handleSubmit} />
        </View>
    );
}

export default AddGroup;