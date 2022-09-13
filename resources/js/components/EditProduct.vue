<template>
    <section>
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="">Product Name</label>
                            <input type="text" v-model="product_name" placeholder="Product Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Product SKU</label>
                            <input type="text" v-model="product_sku" placeholder="Product Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Description</label>
                            <textarea v-model="description" id="" cols="30" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                    </div>
                    <div class="card-body border">
                        <vue-dropzone ref="myVueDropzone" id="dropzone" :options="dropzoneOptions" v-on:vdropzone-success="uploadSuccess"></vue-dropzone>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                    </div>
                    <div class="card-body">
                        <div class="row" v-for="(item,index) in product_variant">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Option</label>
                                    <select v-model="item.option" class="form-control">
                                        <option v-for="variant in variants"
                                                :value="variant.id">
                                            {{ variant.title }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label v-if="product_variant.length != 1" @click="product_variant.splice(index,1); checkVariant"
                                           class="float-right text-primary"
                                           style="cursor: pointer;">Remove</label>
                                    <label v-else for="">.</label>
                                    <input-tag v-model="item.tags" @input="checkVariant" class="form-control"></input-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" v-if="product_variant.length < variants.length && product_variant.length < 3">
                        <button @click="newVariant" class="btn btn-primary">Add another option</button>
                    </div>

                    <div class="card-header text-uppercase">Preview</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <td>Variant</td>
                                    <td>Price</td>
                                    <td>Stock</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="variant_price in product_variant_prices">
                                    <td>{{ variant_price.title }}</td>
                                    <td>
                                        <input type="text" class="form-control" v-model="variant_price.price">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" v-model="variant_price.stock">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button @click="updateProduct" type="submit" class="btn btn-lg btn-primary">Save</button>
        <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
    </section>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import InputTag from 'vue-input-tag'

export default {
    components: {
        vueDropzone: vue2Dropzone,
        InputTag
    },
    props: {
        variants: {
            type: Array,
            required: true
        },
        data:{},
        product_variant_data:{},
        product_variant_price_data:{},
        product_image_data:{},
    },
    data() {
        return {
            product_id: this.data.id,
            product_name: this.data.title,
            product_sku: this.data.sku,
            description: this.data.description,
            images: [],
            product_variant: [
                {
                    option: this.variants[0].id,
                    tags: []
                }
            ],
            product_variant_prices: [],
            dropzoneOptions: {
                url: '/file_upload',
                thumbnailWidth: 150,
                maxFilesize: 0.5,
                headers: {"X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content}
            }
        }
    },
    methods: {
        // it will push a new object into product variant
        newVariant() {
            let all_variants = this.variants.map(el => el.id)
            let selected_variants = this.product_variant.map(el => el.option);
            let available_variants = all_variants.filter(entry1 => !selected_variants.some(entry2 => entry1 == entry2))
            // console.log(available_variants)

            this.product_variant.push({
                option: available_variants[0],
                tags: []
            })
        },
        uploadSuccess(file, response) {
            console.log('File Successfully Uploaded with file name: ' + response.file);
            this.images.push(response.file);
        },

        // check the variant and render all the combination
        checkVariant() {
            let tags = [];
            this.product_variant_prices = [];
            this.product_variant.filter((item) => {
                tags.push(item.tags);
            })

            this.getCombn(tags).forEach(item => {
                this.product_variant_prices.push({
                    title: item,
                    price: 0,
                    stock: 0
                })
            })
        },

        // combination algorithm
        getCombn(arr, pre) {
            pre = pre || '';
            if (!arr.length) {
                return pre;
            }
            let self = this;
            let ans = arr[0].reduce(function (ans, value) {
                return ans.concat(self.getCombn(arr.slice(1), pre + value + '/'));
            }, []);
            return ans;
        },

        // store product into database
        updateProduct() {
            let product = {
                title: this.product_name,
                sku: this.product_sku,
                description: this.description,
                product_image: this.images,
                product_variant: this.product_variant,
                product_variant_prices: this.product_variant_prices
            }


            axios.put('/product/'+this.product_id, product).then(response => {
                console.log(response.data);
                if(response.data.success == "ok"){
                    alert("Product updated successfully")
                }
            }).catch(error => {
                console.log(error);
            })

            console.log(product);
        }


    },
    mounted() {
        // console.log(this.product_variant_price_data);
        console.log('Component mounted.')
        let all_variants = this.variants.map(el => el.id)
        let selected_variants = this.product_variant.map(el => el.option);
        let available_variants = all_variants.filter(entry1 => !selected_variants.some(entry2 => entry1 == entry2))
        // console.log(all_variants)

        this.product_variant = []
        // all_variants.forEach((value, index) => {
        //     let tag_name = 'tags_' + value
        //     console.log(tag_name)
        // })
        let variantsss = all_variants.filter((variant_val)=>{
            let tags = [];
            // let variants_in_this_product = []
            this.product_variant_data.forEach((product_variant_info, index) => {
                // product_variant_info.variant_id
                if(product_variant_info.variant_id == variant_val){
                    tags.push(product_variant_info.variant);
                }
            })
            if(tags.length != 0){
                this.product_variant.push({
                    option: variant_val,
                    tags: tags
                })
            }
            console.log(tags);
        })
        // console.log(tags);
        this.product_variant_price_data.forEach((product_variant_price_info, index) => {
            let item1 = "";
            let item2 = "";
            let item3 = "";
            let item = "";
            this.product_variant_data.forEach((product_variant_info, index) => {
                if(product_variant_info.id == product_variant_price_info.product_variant_one){
                    item1 = product_variant_info.variant;
                }
                if(product_variant_info.id == product_variant_price_info.product_variant_two){
                    item2 = product_variant_info.variant;
                }
                if(product_variant_info.id == product_variant_price_info.product_variant_three){
                    item3 = product_variant_info.variant;
                }
            })
            if(item1){
                item += item1+'/'; 
            }
            if(item2){
                item += item2+'/'; 
            }
            if(item3){
                item += item3; 
            }
            this.product_variant_prices.push({
                title: item,
                price: product_variant_price_info.price,
                stock: product_variant_price_info.stock
            })
            
            // console.log(product_variant_price_info.product_variant_one);
        })
        this.product_image_data.forEach((product_image_info, index) => {
            console.log(product_image_info)
            var file = { size: 123, name: "Icon", type: "image/png" };
            var url = "http://127.0.0.1:8000/images/"+product_image_info.file_path;
            this.$refs.myVueDropzone.manuallyAddFile(file, url);
        })
        
    }
}
</script>
