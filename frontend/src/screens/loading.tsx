import LoadingComponent from '~/components/Loading';
import { Container } from '~/components/Container';

/**
 * Loading screen component.
 * 
 * This component displays a centered loading spinner using the LoadingComponent
 * during content or data loading
 * 
 */
const Loading = () => {
    return (
        <Container className="flex justify-center items-center">
            <LoadingComponent reverseColors />
        </Container>
    );
}

export default Loading;

