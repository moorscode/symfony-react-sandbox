import React, {useEffect} from "react";
import Actions from "../actions/recipesActions";
import {connect} from "react-redux";
import RecipeSearchList from "../../common/components/RecipeSearchList";

const Recipes = ({recipes, baseUrl, fetching, dispatch}) => {

    useEffect(() => {
        if (recipes) {
            return;
        }

        dispatch(Actions.fetchRecipes(baseUrl));
    }, []);

    if (fetching || !recipes) {
        return <div>Loading...</div>;
    }

    return (
        <div>
            <ol className="breadcrumb">
                <li className="active">Recipes</li>
            </ol>
            <RecipeSearchList recipes={recipes} routePrefix={""}/>
        </div>
    );
}


const mapStateToProps = state => ({
    recipes: state.recipesState.recipes,
    fetching: state.recipesState.fetching,
    baseUrl: state.recipesState.baseUrl
});

export default connect(mapStateToProps)(Recipes);
