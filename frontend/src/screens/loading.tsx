import LoadingComponent from '~/components/Loading';
import { Container } from '~/components/Container';

const Loading = () => {
    return (
        <Container className="flex justify-center items-center">
            <LoadingComponent reverseColors />
        </Container>
    );
}

export default Loading;

