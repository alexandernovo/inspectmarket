<script>
    // Step 1: Send email
    $("#forgotPasswordForm").submit(function(e) {
        e.preventDefault();
        $("#btnSendMail").prop("disabled", true).text("Sending...");
        $.post("{{ route('forgot.password.send') }}", $(this).serialize(), function(res) {
            $("#fpAlert").removeClass("d-none alert-danger").addClass("alert-success").html(res
                .message);
            $("#btnSendMail").prop("disabled", false).text("Send Reset Link");
            if (res.status) {
                $("#step1").addClass("d-none");
                $("#step2").removeClass("d-none");
            }
        }).fail(function(err) {
            $("#fpAlert").removeClass("d-none alert-success").addClass("alert-danger").html(err
                .responseJSON?.message || "Error");
            $("#btnSendMail").prop("disabled", false).text("Send Reset Link");
        });
    });

    // Step 2: Verify code
    $("#verifyCodeBtn").click(function() {
        $.post("{{ route('forgot.password.verify') }}", {
            code: $("#verifyCode").val(),
            _token: "{{ csrf_token() }}"
        }, function(res) {
            if (res.status) {
                window.sessionStorage.setItem('fpToken', res.token);
                $("#step2").addClass("d-none");
                $("#step3").removeClass("d-none");
            } else {
                $("#codeAlert").removeClass("d-none alert-success").addClass("alert-danger").html(res
                    .message);
            }
        });
    });

    // Step 3: Reset password
    $("#finishReset").click(function() {
        let token = window.sessionStorage.getItem('fpToken');
        let pass = $("#newPassword").val(),
            conf = $("#confirmPassword").val();
        if (pass !== conf) {
            $("#resetAlert").removeClass("d-none alert-success").addClass("alert-danger").html(
                "Passwords do not match");
            return;
        }
        $.post("{{ route('forgot.password.reset') }}", {
            token: token,
            password: pass,
            password_confirmation: conf,
            _token: "{{ csrf_token() }}"
        }, function(res) {
            if (res.status) {
                $("#step3").addClass("d-none");
                $("#step4").removeClass("d-none");
            } else {
                $("#resetAlert").removeClass("d-none alert-success").addClass("alert-danger").html(res
                    .message);
            }
        });
    });

    $("#backToStep1").click(function() {
        $("#step2").addClass("d-none");
        $("#step1").removeClass("d-none");
        $("#codeAlert").addClass("d-none").html('');
    });

    $("#backToStep2").click(function() {
        $("#step3").addClass("d-none");
        $("#step2").removeClass("d-none");
        $("#resetAlert").addClass("d-none").html('');
    });
</script>
