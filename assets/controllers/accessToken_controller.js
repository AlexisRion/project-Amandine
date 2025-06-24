import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    //TODO connect when logged in
    connect() {
        console.log('accessToken Controller connected');
    }

    //TODO disconnect when logged out
    disconnect() {
        console.log('accessToken Controller disconnected');
    }
}
