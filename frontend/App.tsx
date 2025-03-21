import './global.css';

import 'react-native-gesture-handler';

import { Provider } from 'react-redux';
import store from './src/store';
import Navigation from './src/navigation';
// import { GestureHandlerRootView } from 'react-native-gesture-handler';

export default function App() {
  return (
    // <GestureHandlerRootView style={{ flex: 1 }}>
      <Provider store={store}>
          <Navigation />
      </Provider>
    // </GestureHandlerRootView>
  );
}
