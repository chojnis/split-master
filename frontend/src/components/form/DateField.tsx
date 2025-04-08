import React, { useState } from 'react';
import { View, Pressable } from 'react-native';
import DatePicker from 'react-native-date-picker'
import { Text } from '~/components/ui/text';
import { FormFieldValue } from '~/components/form/FormField';
import { formatDateNicely } from '~/lib/utils';

interface DateFieldProps {
    value: Date;
    onChange: (date: FormFieldValue) => void;
}

const DateField: React.FC<DateFieldProps> = ({
    value,
    onChange
}) => {
    const [show, setShow] = useState<boolean>(false);

    return (
        <View className="top-0 left-0 bottom-0 right-0 absolute z-10 w-full flex px-4 items-start justify-center">
            <Pressable
                onPress={() => setShow(true)}
            >
                <Text className="text-lg">
                    {formatDateNicely(value, true)}
                </Text>
            </Pressable>
            
            <DatePicker
                modal
                date={value as Date}
                open={show}
                onConfirm={(date) => {
                    setShow(false);
                    onChange(date);
                }}
                onCancel={() => {
                    setShow(false);
                }}
            />
        </View>
    );
};

export default DateField;