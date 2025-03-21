import { RouteProp, useRoute } from '@react-navigation/native';
import { ScreenContent } from '~/components/ScreenContent';
import { StyleSheet, View } from 'react-native';

import { RootTabParamList } from '../../navigation';

type AddTransactionScreenRouteProp = RouteProp<RootTabParamList, 'AddTransaction'>;

export default function AddTransaction() {
  const router = useRoute<AddTransactionScreenRouteProp>();

  return (
    <View style={styles.container}>
      <ScreenContent
        path="screens/addTransaction.tsx"
        title={`Showing addTransaction screen`}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 6,
  },
});
