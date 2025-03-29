import './global.css';

import { Provider } from 'react-redux';
import { setupStore, AppStore } from './src/store';
import Navigation from './src/navigation';
import { useEffect, useState } from 'react';
import LoadingScreen from './src/screens/loading';

let store: AppStore | undefined;

export default function App() {
  const [isAppReady, setAppReady] = useState(false);

  useEffect(() => {
    if(isAppReady) return;
    setupStore().then((newStore) => {
      store = newStore;
      setAppReady(true);
    });
  }, []);

  if (!isAppReady || !store) {
      return <LoadingScreen />;
  }
  
  return (
      <Provider store={store}>
          <Navigation />
      </Provider>
  );
}
