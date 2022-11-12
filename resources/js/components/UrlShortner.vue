<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="heading text-center">
                <h1>URL Shortener</h1>
            </div>
            <form action="">
                <div class="form-group">
                    <div v-if="errorMessage">
                        <ul class="alert alert-danger">
                            <li v-for="(value, key, index) in errorMessage">{{ value }}</li>
                        </ul>
                    </div>
                    <div v-if="successMessage" class="alert alert-success" role="alert">
                        Succesfully Created!
                    </div>
                   
                    <input type="text" class="form-control" v-model="main_url" id="shortUrl" aria-describedby="Enter your full url to be short" placeholder="Shorten your link">
                </div>
                <br>
                <div class="button-center text-center mt-20">
                    <button type="submit" v-on:click.prevent="shorturl" class="btn btn-primary">Shorten</button>
                </div>
                <br>
                <div class="card" v-if="short_url">
                    <div class="card-body">
                        <p class="card-text originnal-url"></p>
                        <a v-on:click.prevent="gothLink(short_url)" href="#" id="shorted-url">{{short_url}}</a>
                        <br>
                        <a href="#" class="btn btn-primary">Copy</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data(){
            return {
                main_url:null,
                old_url: null,
                short_url: null,
                errorMessage: null,
                successMessage: false,
            }
        },
        methods:{
            shorturl(){
                let self = this;
                let mainUrl = self.main_url;

                axios.post('/url/shortner', {
                    mainUrl: mainUrl
                })
                .then(
                    response => {
                        self.main_url = '';
                        self.successMessage = true;
                        self.errorMessage = null,
                        self.short_url = response.data
                    }
                ).catch(error => {

                    self.main_url = '';
                    self.successMessage = false;
                    self.errorMessage = error.response.data.mainUrl;
                    if( typeof error.response.data.shortUrl === 'object' && error.response.data.shortUrl !== null)   
                    {
                        self.short_url =  error.response.data.shortUrl[0];
                        self.old_url = mainUrl;
                    } else {
                        self.short_url =  '';
                        self.old_url = '';
                    }
                    console.error("There was an error!", error);

                });
            },
            gothLink(link){
                window.open(link);
            }
        }
    }
</script>
