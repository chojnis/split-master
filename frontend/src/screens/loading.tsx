import LoadingComponent from '~/components/Loading';
import { Container } from '~/components/Container';

const Loading = () => {
    return (
        <Container>
            <LoadingComponent reverseColors />
        </Container>
    );
}

export default Loading;

