@extends('layout.app')


@section('content')

    @include('users.components.topNav')
    <div id="user-wrapper">
        @include('users.components.sideNav')
        @include('users.components.post')
        @include('users.components.profile')
        @include('users.components.project')
    </div>

@endsection

@section('script')
    <script type="text/javascript">
    /*

        CK-EDITOR

    */
    // $("#post-editor").ckeditor(function(){
    //     $("#postSubmitConfirmBtn").click(function(){
    //         let postValue = $("#post-editor").val();
    //         console.log(postValue);
    //     });
    // });

    /* side nav show/hide  */
    let menuBarCount = 0;
    $("#userMenuBarBtn").click(function(){
        if(menuBarCount%2==0)
        {
            $("#userSideNav").removeClass("d-none");
        }
        else{
            $("#userSideNav").addClass("d-none");
        }
        menuBarCount++;
    })

    $("#user_project").click(function(){
        $("#user_post_show").addClass("d-none");
        $("#user_profile_show").addClass("d-none");
        $("#user_project_show").removeClass("d-none");

        $("#addNewProjectBtn").click(function(){
            $("#addNewProjectModal").modal("show");
        })


    });

    $("#user_post").click(function(){
        $("#user_profile_show").addClass("d-none");
        $("#user_project_show").addClass("d-none");
        $("#user_post_show").removeClass("d-none");

        $("#addNewPostBtn").click(function(){
            $("#create_post").removeClass("d-none");

            $("#postConfirmSubmitBtn").click(function(){
                console.log("Post Submit");
                let postHtmlText = postTextField.document.getElementsByTagName('body')[0].innerHTML;
                console.log(postHtmlText);
            });
        })
        $("#addNewPostBtn").dblclick(function(){
            $("#create_post").addClass("d-none");
        })


    });

    /* User Profile */

    let edu_count = 0;
    function getUserProfile()
    {
        edu_count = 0;
        axios.get('/users/getProfile')
            .then(function(res){
                let user = res.data;
                if(res.status==200)
                {
                    let split_url= user.image.split('/');
                    let imgUrl = split_url[split_url.length-2]==="default" ? "{!!asset('"+user.image+"')!!}" : user.image;
                    $("#user_profile_image").attr('src',imgUrl);
                    $("#user_name").val(user.name)
                    $("#user_email").text(user.email);
                    $("#user_short_desc").val(user.description)
                    const social_link = JSON.parse(user.social_link);
                    // console.log(social_link.facebook);
                    $("#user_github").val(social_link.github);
                    $("#user_linkedin").val(social_link.linkedin);
                    $("#user_facebook").val(social_link.facebook);
                    let education = JSON.parse(user.education);
                    let today = new Date();
                    let month = today.getMonth()+1 < 10 ? "0"+(today.getMonth()+1) : (today.getMonth()+1);
                    let today_date = today.getFullYear()+"-"+month+"-"+today.getDate();
                    // console.log(today_date);
                    $("#educationAppend").empty();
                    $.each(education,function(idx,item){
                        if(education[idx].end_time === "present"){
                            $('<p>').html(
                                '<input type="text" placeholder="institution name" class="user_institute'+ edu_count +'" value="'+ education[idx].institute_name +'" /></br>' +
                                '<input type="date" placeholder="institute" class="start_time'+ edu_count +'" value="'+education[idx].start_time +'" /></br>'+ 
                                '<input type="date" placeholder="institute" class="end_time'+ edu_count +'" value="'+ today_date +'" /></br>'
                            ).appendTo("#educationAppend");
                        }
                        else{
                            $('<p>').html(
                                '<input type="text" placeholder="institution name" class="user_institute'+ edu_count +'" value="'+ education[idx].institute_name +'" /></br>' +
                                '<input type="date" placeholder="institute" class="start_time'+ edu_count +'" value="'+education[idx].start_time +'" /></br>'+ 
                                '<input type="date" placeholder="institute" class="end_time'+ edu_count +'" value="'+education[idx].end_time +'" /></br>'
                            ).appendTo("#educationAppend");
                        }
                        edu_count++;
                    })
                }
                else{
                    console.log("user get profile wrong");
                }
            })
            .catch(function(error){
                console.log(error.response);
            });
    }
    $("#user_profile").click(function(){
        $("#user_post_show").addClass("d-none");
        $("#user_project_show").addClass("d-none");
        $("#user_profile_show").removeClass("d-none");
        
        
        getUserProfile();
        let descriptionUpdate = '';
        $("#user_short_desc").change(function(){
            descriptionUpdate = $(this).val();
        });
        $("#update_short_desc_btn").click(function(){
            if(descriptionUpdate.trim().length>0)
            {
                axios.post('users/descriptionUpdate',{description:descriptionUpdate})
                .then(function(res){
                    if(res.status==200)
                    {
                        getUserProfile();
                    }
                })
                .catch(function(error){
                    console.log(error.response);
                });
            }
        });
        let facebookUrl = '',linkedinUrl='',githubUrl = '';
        $("#user_facebook").change(function(){
            facebookUrl = $(this).val().trim();
            githubUrl = $("#user_github").val().trim();
            linkedinUrl = $("#user_linkedin").val().trim();
        });
        $("#user_linkedin").change(function(){
            linkedinUrl = $(this).val().trim();
            githubUrl = $("#user_github").val().trim();
            facebookUrl = $("#user_facebook").val().trim();
        });
        $("#user_github").change(function(){
            githubUrl = $(this).val().trim();
            linkedinUrl = $("#user_linkedin").val().trim();
            facebookUrl = $("#user_facebook").val().trim();
        });
        $("#update_social_link_btn").click(function(){
            if(facebookUrl.length>0||linkedinUrl.length>0||githubUrl.length>0)
            {
                //console.log(facebookUrl+"\n"+linkedinUrl+"\n"+githubUrl);
                const linkObj = {
                    facebook : facebookUrl,
                    linkedin : linkedinUrl,
                    github : githubUrl
                };
                const linked_stringify = JSON.stringify(linkObj);
                axios.post('users/socialLinkUpdate',{
                    social_link : linked_stringify
                })
                .then(function(res){
                    if(res.status==200)
                    {
                        getUserProfile();
                    }
                })
                .catch(function(error){
                    console.log(error.response);
                })
            }
        })
        
        $("#update_profile_image").change(function(){
            $("#profile_image_error").addClass("d-none");
            let profileImgFile = $(this).prop('files')[0];
            let current_image_url =  $("#user_profile_image").attr('src');
            // console.log("current image : "+current_image_url);
            if(profileImgFile.size <= 1024 * 1000)
            {
                let fileReader = new FileReader();
                fileReader.readAsDataURL(this.files[0]);
                fileReader.onload = (event) =>{
                    let imgUrl = event.target.result;
                    $("#user_profile_image").attr('src',imgUrl);
                }

                $("#update_profile_image_btn").click(function(){
                    let formData = new FormData();
                    let imgFile = profileImgFile;;
                    formData.append('profile_image',imgFile);
                    formData.append('current_image_url',current_image_url);
                    axios.post('/users/imageUpdate',formData)
                        .then((res)=>{
                            if(res.status==200)
                            {
                                getUserProfile();
                            }
                        })
                        .catch((error)=>{
                            console.log(error.response);
                        })

                    // console.log("adds ok")
                    // console.log(imgFile);
                });
            }
            else{
                $("#profile_image_error").removeClass("d-none");
            }
            // console.log(profileImgFile);
        });


        let education_info = [];
        $("#addEducation").click(function(){
            // console.log("add Education");
            $('<p>').html(
                '<input type="text" placeholder="institution name" class="user_institute'+ edu_count +'" /></br>' +
                '<input type="date" placeholder="institute" class="start_time'+ edu_count +'" /></br>' +
                '<input type="date" placeholder="institute" class="end_time'+ edu_count +'" /></br>'
            ).appendTo("#educationAppend");
            edu_count++;
        });
        $("#update_education_info_btn").click(function(){
            for(let idx=0;idx<edu_count;idx++)
            {
                let user_institute_class = ".user_institute"+idx;
                let start_time_class = ".start_time"+idx;
                let end_time_class = ".end_time"+idx;
                let today = new Date();
                let month = today.getMonth()+1 < 10 ? "0"+(today.getMonth()+1) : (today.getMonth()+1);
                // console.log("month : "+month);
                let today_date = today.getFullYear()+"-"+month+"-"+today.getDate();
                // console.log(today_date);
                let user_institute = $(user_institute_class).val().trim();
                let start_times = $(start_time_class).val().trim();
                let end_times = $(end_time_class).val().trim();
                if(user_institute.length>2){
                    let user_edu_obj = {
                        institute_name : user_institute,
                        start_time : start_times,
                        end_time : end_times===today_date ? "present" : end_times
                    }
                    education_info.push(user_edu_obj);
                }
            }
            if(education_info.length>0)
            {
                // console.log(JSON.stringify(education_info));
                axios.post('users/educationUpdate',{
                    education : JSON.stringify(education_info)
                }).then(function(res){
                    if(res.status==200)
                    {
                        getUserProfile();
                    }
                })
                .catch(function(error){
                    
                })
            }
            else{
                alert("Your Education Field is empty");
            }
        });
    });
    </script>
@endsection